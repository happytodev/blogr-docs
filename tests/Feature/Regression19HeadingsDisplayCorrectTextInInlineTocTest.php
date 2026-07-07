<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

beforeEach(function () {
    $this->parent = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $this->parent->id,
        'locale' => 'en',
        'title' => 'Parent Doc',
        'slug' => 'parent',
        'content' => '# Parent doc with children below',
    ]);

    $child = DocArticle::create([
        'parent_id' => $this->parent->id,
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $child->id,
        'locale' => 'en',
        'title' => 'Child',
        'slug' => 'child',
        'content' => <<<MD
# Child

## Apple

Apple details.

## Banana

Banana details.

## Cherry

Cherry details.
MD,
    ]);

    $this->response = $this->get('/docs/parent');
});

it('renders each H2 heading with its correct text in the inline TOC', function () {
    $this->response->assertStatus(200);

    $content = $this->response->getContent();
    preg_match('/In this section.*?<\/section>/s', $content, $match);
    $inlineTocHtml = $match[0] ?? '';

    $this->assertStringContainsString('Apple', $inlineTocHtml);
    $this->assertStringContainsString('Banana', $inlineTocHtml);
    $this->assertStringContainsString('Cherry', $inlineTocHtml);
});

it('displays distinct H2 heading texts in the correct order', function () {
    $this->response->assertSeeInOrder([
        'Apple',
        'Banana',
        'Cherry',
    ]);
});
