<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('can search articles by title', function () {
    createSearchableArticle('Linux Commands', 'linux-commands');
    createSearchableArticle('Windows Guide', 'windows-guide');

    $results = DocArticleTranslation::where('locale', 'en')
        ->where(function ($q) {
            $q->where('title', 'like', '%Linux%')
              ->orWhere('content', 'like', '%Linux%');
        })
        ->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Linux Commands');
});

it('can search articles by content', function () {
    createSearchableArticle('Bash Guide', 'bash-guide', 'Learn about grep and awk');
    createSearchableArticle('Python Guide', 'python-guide', 'Learn about functions');

    $results = DocArticleTranslation::where('locale', 'en')
        ->where(function ($q) {
            $q->where('title', 'like', '%grep%')
              ->orWhere('content', 'like', '%grep%');
        })
        ->get();

    expect($results)->toHaveCount(1);
});

it('returns no results for non-matching search', function () {
    createSearchableArticle('Linux', 'linux');

    $results = DocArticleTranslation::where('locale', 'en')
        ->where(function ($q) {
            $q->where('title', 'like', '%NonExistent%')
              ->orWhere('content', 'like', '%NonExistent%');
        })
        ->get();

    expect($results)->toBeEmpty();
});

it('searches only published articles', function () {
    $published = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $published->id, 'locale' => 'en',
        'title' => 'Published Guide', 'slug' => 'published-guide',
        'content' => 'Some content',
    ]);

    $unpublished = DocArticle::create([
        'position' => 1, 'is_published' => false,
        'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $unpublished->id, 'locale' => 'en',
        'title' => 'Draft Guide', 'slug' => 'draft-guide',
        'content' => 'Some content',
    ]);

    $results = DocArticleTranslation::whereHas('article', function ($q) {
        $q->where('is_published', true);
    })
        ->where('locale', 'en')
        ->where(function ($q) {
            $q->where('title', 'like', '%Guide%')
              ->orWhere('content', 'like', '%Guide%');
        })
        ->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Published Guide');
});

function createSearchableArticle(string $title, string $slug, ?string $extraContent = null): DocArticle
{
    $article = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => $title, 'slug' => $slug,
        'content' => '# '.$title."\n\n".($extraContent ?? 'Content about '.$title),
    ]);

    return $article;
}
