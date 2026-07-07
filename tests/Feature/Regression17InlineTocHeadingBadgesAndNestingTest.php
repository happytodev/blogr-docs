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
        'content' => '# Parent' . "\n\n" . '## Parent Heading',
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
        'content' => '# Child' . "\n\n" . '## Setup' . "\n\n" . '### Requirements',
    ]);

    $this->response = $this->get('/docs/parent');
});

it('does not display H2 or H3 heading level badges in inline TOC', function () {
    $content = $this->response->getContent();
    preg_match('/In this section.*?<\/section>/s', $content, $match);
    $inlineTocHtml = $match[0] ?? '';

    $this->assertStringNotContainsString('H2', $inlineTocHtml);
    $this->assertStringNotContainsString('H3', $inlineTocHtml);
});

it('nests H3 headings under their parent H2 in the inline TOC', function () {
    $this->response->assertSeeInOrder([
        'Setup',
        '<ul',
        'Requirements',
    ]);
});
