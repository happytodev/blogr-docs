<?php

namespace Happytodev\BlogrDocs\Models;

use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocArticleTranslation extends Model
{
    use ClearsLocaleCache;

    protected $fillable = [
        'doc_article_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'headings',
    ];

    protected function casts(): array
    {
        return [
            'headings' => 'array',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(DocArticle::class, 'doc_article_id');
    }

    public function url(): string
    {
        $prefix = config('blogr-docs.prefix', 'docs');
        $localesEnabled = config('blogr.locales.enabled', false);

        $slugs = [$this->slug];
        $parent = $this->article->parent;

        while ($parent) {
            $parentTranslation = $parent->translation($this->locale) ?? $parent->defaultTranslation();
            if ($parentTranslation) {
                array_unshift($slugs, $parentTranslation->slug);
            }
            $parent = $parent->parent;
        }

        $path = implode('/', $slugs);

        if ($localesEnabled) {
            if ($route = \Route::getRoutes()->getByName('blogr-docs.show')) {
                return route('blogr-docs.show', ['locale' => $this->locale, 'path' => $path]);
            }

            return '/'.$this->locale.'/'.$prefix.'/'.$path;
        }

        if ($route = \Route::getRoutes()->getByName('blogr-docs.show')) {
            return route('blogr-docs.show', ['path' => $path]);
        }

        return '/'.$prefix.'/'.$path;
    }

    public function excerptHtml(): string
    {
        if (empty($this->excerpt)) {
            return '';
        }

        return strip_tags($this->excerpt);
    }
}
