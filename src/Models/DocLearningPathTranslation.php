<?php

namespace Happytodev\BlogrDocs\Models;

use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocLearningPathTranslation extends Model
{
    use ClearsLocaleCache;

    protected $fillable = [
        'doc_learning_path_id',
        'locale',
        'title',
        'slug',
        'description',
    ];

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(DocLearningPath::class, 'doc_learning_path_id');
    }

    public function url(): string
    {
        return '/'.config('blogr-docs.prefix', 'docs').'/learning-path/'.$this->slug;
    }
}
