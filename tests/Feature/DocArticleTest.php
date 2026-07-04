<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('creates an article with full translation', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'display_toc' => true,
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Linux Fundamentals',
        'slug' => 'linux-fundamentals',
        'excerpt' => 'Learn the basics of Linux',
        'content' => '# Linux\n\nThis is a guide.',
        'seo_title' => 'Linux Guide',
        'seo_description' => 'Complete Linux guide',
    ]);

    $article->refresh();
    $article->load('translations');

    expect($article->translations)->toHaveCount(1);
    expect($article->translation('en')->title)->toBe('Linux Fundamentals');
    expect($article->translation('en')->seo_title)->toBe('Linux Guide');
});

it('returns breadcrumbs from root to article', function () {
    $root = DocArticle::create(['position' => 0, 'default_locale' => 'en']);
    DocArticleTranslation::create([
        'doc_article_id' => $root->id, 'locale' => 'en',
        'title' => 'Sys', 'slug' => 'sys',
    ]);

    $child = DocArticle::create(['parent_id' => $root->id, 'position' => 0, 'default_locale' => 'en']);
    DocArticleTranslation::create([
        'doc_article_id' => $child->id, 'locale' => 'en',
        'title' => 'Linux', 'slug' => 'linux',
    ]);

    $grandchild = DocArticle::create(['parent_id' => $child->id, 'position' => 0, 'default_locale' => 'en']);
    DocArticleTranslation::create([
        'doc_article_id' => $grandchild->id, 'locale' => 'en',
        'title' => 'Commands', 'slug' => 'commands',
    ]);

    $breadcrumbs = $grandchild->getBreadcrumbs('en');

    expect($breadcrumbs)->toHaveCount(3);
    expect($breadcrumbs[0]['title'])->toBe('Sys');
    expect($breadcrumbs[1]['title'])->toBe('Linux');
    expect($breadcrumbs[2]['title'])->toBe('Commands');
});

it('respects tree ordering by position', function () {
    $parent = DocArticle::create(['position' => 0, 'default_locale' => 'en']);

    $third = DocArticle::create(['parent_id' => $parent->id, 'position' => 2, 'default_locale' => 'en']);
    $first = DocArticle::create(['parent_id' => $parent->id, 'position' => 0, 'default_locale' => 'en']);
    $second = DocArticle::create(['parent_id' => $parent->id, 'position' => 1, 'default_locale' => 'en']);

    $children = $parent->children()->get();

    expect($children[0]->id)->toBe($first->id);
    expect($children[1]->id)->toBe($second->id);
    expect($children[2]->id)->toBe($third->id);
});

it('generates url with full slug path', function () {
    $root = DocArticle::create(['position' => 0, 'default_locale' => 'en']);
    $rootTranslation = DocArticleTranslation::create([
        'doc_article_id' => $root->id, 'locale' => 'en',
        'title' => 'Admin', 'slug' => 'admin',
    ]);

    $child = DocArticle::create(['parent_id' => $root->id, 'position' => 0, 'default_locale' => 'en']);
    $childTranslation = DocArticleTranslation::create([
        'doc_article_id' => $child->id, 'locale' => 'en',
        'title' => 'Linux', 'slug' => 'linux',
    ]);

    $url = $childTranslation->url();

    expect($url)->toContain('/docs/admin/linux');
});

it('prevents duplicate slug within same locale', function () {
    $article = DocArticle::create(['position' => 0, 'default_locale' => 'en']);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'Guide', 'slug' => 'guide',
    ]);

    $article2 = DocArticle::create(['position' => 1, 'default_locale' => 'en']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    DocArticleTranslation::create([
        'doc_article_id' => $article2->id, 'locale' => 'en',
        'title' => 'Guide Dupe', 'slug' => 'guide',
    ]);
});
