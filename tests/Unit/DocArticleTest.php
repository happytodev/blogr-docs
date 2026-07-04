<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('can create a root article', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'default_locale' => 'en',
    ]);

    expect($article->id)->toBeInt();
    expect($article->isRoot())->toBeTrue();
    expect($article->is_published)->toBeTrue();
});

it('can create an article with translation', function () {
    $article = DocArticle::create([
        'position' => 1,
        'is_published' => true,
        'default_locale' => 'en',
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'content' => '# Welcome',
    ]);

    expect($article->translations()->count())->toBe(1);
    expect($article->translation('en')->title)->toBe('Getting Started');
});

it('can create parent-child hierarchy', function () {
    $parent = DocArticle::factory()->create(['position' => 0]);
    $child = DocArticle::factory()->create([
        'parent_id' => $parent->id,
        'position' => 0,
    ]);

    expect($parent->children()->count())->toBe(1);
    expect($child->parent->id)->toBe($parent->id);
});

it('prevents circular parent reference', function () {
    $this->expectException(\RuntimeException::class);

    $article = DocArticle::create(['position' => 0]);
    $article->parent_id = $article->id;
    $article->save();
});

it('scope published returns only published articles', function () {
    DocArticle::create(['position' => 0, 'is_published' => true]);
    DocArticle::create(['position' => 1, 'is_published' => false]);

    expect(DocArticle::published()->count())->toBe(1);
});

it('scope root returns articles without parent', function () {
    DocArticle::create(['position' => 0, 'is_published' => true]);
    $parent = DocArticle::create(['position' => 1, 'is_published' => true]);
    DocArticle::create(['parent_id' => $parent->id, 'position' => 0, 'is_published' => true]);

    expect(DocArticle::root()->count())->toBe(2);
});

it('isLeaf returns true when article has no children', function () {
    $article = DocArticle::create(['position' => 0]);

    expect($article->isLeaf())->toBeTrue();
});

it('isOverview returns true when article has children', function () {
    $parent = DocArticle::create(['position' => 0]);
    DocArticle::create(['parent_id' => $parent->id, 'position' => 0]);

    expect($parent->isOverview())->toBeTrue();
});
