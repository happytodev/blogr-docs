<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('registers docs routes', function () {
    $routes = collect(app('router')->getRoutes()->getRoutesByName());

    expect($routes->has('blogr-docs.index'))->toBeTrue();
    expect($routes->has('blogr-docs.show'))->toBeTrue();
});

it('resolves path segments correctly', function () {
    $article = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'Admin', 'slug' => 'admin',
    ]);

    $child = DocArticle::create([
        'parent_id' => $article->id, 'position' => 0,
        'is_published' => true, 'published_at' => now(),
        'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $child->id, 'locale' => 'en',
        'title' => 'Linux', 'slug' => 'linux',
    ]);

    $resolved = (new \Happytodev\BlogrDocs\Helpers\DocTreeHelper)
        ->resolvePath(['admin', 'linux'], 'en');

    expect($resolved)->not->toBeNull();
    expect($resolved->id)->toBe($child->id);
});

it('returns null for unresolved path', function () {
    $result = (new \Happytodev\BlogrDocs\Helpers\DocTreeHelper)
        ->resolvePath(['unknown'], 'en');

    expect($result)->toBeNull();
});

it('excludes unpublished articles from resolution', function () {
    $article = DocArticle::create([
        'position' => 0, 'is_published' => false,
        'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'Draft', 'slug' => 'draft',
    ]);

    $result = (new \Happytodev\BlogrDocs\Helpers\DocTreeHelper)
        ->resolvePath(['draft'], 'en');

    expect($result)->toBeNull();
});
