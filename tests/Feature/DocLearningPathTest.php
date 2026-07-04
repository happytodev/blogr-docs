<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Happytodev\BlogrDocs\Models\DocLearningPath;
use Happytodev\BlogrDocs\Models\DocLearningPathTranslation;

it('can create a learning path', function () {
    $path = DocLearningPath::create([
        'position' => 0,
        'is_published' => true,
    ]);

    DocLearningPathTranslation::create([
        'doc_learning_path_id' => $path->id,
        'locale' => 'en',
        'title' => 'Linux Basics',
        'slug' => 'linux-basics',
        'description' => 'Learn Linux from scratch',
    ]);

    expect($path->translations()->count())->toBe(1);
    expect($path->translation('en')->title)->toBe('Linux Basics');
});

it('can order articles within a learning path', function () {
    $path = DocLearningPath::create(['position' => 0, 'is_published' => true]);
    DocLearningPathTranslation::create([
        'doc_learning_path_id' => $path->id, 'locale' => 'en',
        'title' => 'Path', 'slug' => 'path',
    ]);

    $first = createDocArticle('Intro', 'intro');
    $second = createDocArticle('Advanced', 'advanced');
    $third = createDocArticle('Expert', 'expert');

    $path->articles()->attach($first->id, ['position' => 0]);
    $path->articles()->attach($third->id, ['position' => 2]);
    $path->articles()->attach($second->id, ['position' => 1]);

    $path->load('articles');

    expect($path->articles[0]->id)->toBe($first->id);
    expect($path->articles[1]->id)->toBe($second->id);
    expect($path->articles[2]->id)->toBe($third->id);
});

it('scopes published learning paths', function () {
    DocLearningPath::create(['position' => 0, 'is_published' => true]);
    DocLearningPath::create(['position' => 1, 'is_published' => false]);

    $published = DocLearningPath::published()->get();
    expect($published)->toHaveCount(1);
});

it('scopes ordered learning paths', function () {
    $second = DocLearningPath::create(['position' => 2, 'is_published' => true]);
    $first = DocLearningPath::create(['position' => 1, 'is_published' => true]);

    $ordered = DocLearningPath::ordered()->get();
    expect($ordered[0]->id)->toBe($first->id);
    expect($ordered[1]->id)->toBe($second->id);
});

function createDocArticle(string $title, string $slug): DocArticle
{
    $article = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => $title, 'slug' => $slug,
        'content' => '# '.$title,
    ]);

    return $article;
}
