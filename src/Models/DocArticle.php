<?php

namespace Happytodev\BlogrDocs\Models;

use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocArticle extends Model
{
    use ClearsLocaleCache, HasFactory;

    protected $fillable = [
        'parent_id',
        'position',
        'icon',
        'is_published',
        'published_at',
        'default_locale',
        'display_toc',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'display_toc' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function publishedChildren(): HasMany
    {
        return $this->children()->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocArticleTranslation::class, 'doc_article_id');
    }

    public function translation(string $locale): ?DocArticleTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    public function defaultTranslation(): ?DocArticleTranslation
    {
        $locale = $this->default_locale ?? config('app.locale', 'en');

        return $this->translation($locale) ?? $this->translations()->first();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('doc_articles.position');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return ! $this->children()->exists();
    }

    public function isOverview(): bool
    {
        return $this->children()->exists();
    }

    public function getBreadcrumbs(string $locale): array
    {
        $crumbs = [];
        $article = $this;

        while ($article) {
            $translation = $article->translation($locale) ?? $article->defaultTranslation();

            if ($translation) {
                array_unshift($crumbs, [
                    'title' => $translation->title,
                    'url' => $translation->url(),
                ]);
            }

            $article = $article->parent;
        }

        return $crumbs;
    }

    protected static function newFactory(): Factory
    {
        return \Happytodev\BlogrDocs\Database\Factories\DocArticleFactory::new();
    }

    protected static function booted(): void
    {
        static::saving(function (DocArticle $article) {
            if ($article->parent_id && $article->id === $article->parent_id) {
                throw new \RuntimeException('An article cannot be its own parent.');
            }
        });
    }
}
