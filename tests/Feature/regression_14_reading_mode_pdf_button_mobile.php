<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

beforeEach(function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'display_toc' => false,
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '# Hello World',
    ]);

    $this->article = $article;
});

it('hides reading mode button on mobile screens', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertSee('hidden lg:inline-flex');
});

it('shows reading mode button on desktop screens', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertSee('lg:inline-flex');
});

it('renders pdf link with responsive classes p-2 lg:px-4 lg:py-2', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertSee('p-2 lg:px-4 lg:py-2');
});

it('renders pdf link with icon-only text on mobile using hidden lg:inline', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertSee('<span class="hidden lg:inline">', false);
});

it('renders pdf link with aria-label containing Download PDF', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertSee('aria-label="', false);
    $response->assertSee('Download PDF');
});

it('renders pdf link without text-sm font-medium classes', function () {
    $response = $this->get(route('blogr-docs.show', ['path' => 'test-article']));

    $response->assertStatus(200);
    $response->assertDontSee('text-sm font-medium');
});
