<?php

namespace Happytodev\BlogrDocs\Models;

use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocLearningPath extends Model
{
    use ClearsLocaleCache;

    protected $fillable = [
        'icon',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocLearningPathTranslation::class, 'doc_learning_path_id');
    }

    public function translation(string $locale): ?DocLearningPathTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(DocArticle::class, 'doc_learning_path_article')
            ->withPivot('position')
            ->orderBy('doc_learning_path_article.position');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('doc_learning_paths.position');
    }
}
