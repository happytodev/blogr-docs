<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('extracts h2 and h3 headings from markdown content on save', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Test',
        'slug' => 'test',
        'content' => <<<MD
# Title

## Installation

Some content here.

### Prerequisites

Prerequisites content.

## Configuration

More content.

### Option A

Option A details.

### Option B

Option B details.

## Usage

Final section.
MD,
    ]);

    expect($translation->headings)->toBeArray();
    expect($translation->headings)->toHaveCount(6);

    expect($translation->headings[0])->toBe(['level' => 2, 'text' => 'Installation', 'anchor' => 'installation']);
    expect($translation->headings[1])->toBe(['level' => 3, 'text' => 'Prerequisites', 'anchor' => 'prerequisites']);
    expect($translation->headings[2])->toBe(['level' => 2, 'text' => 'Configuration', 'anchor' => 'configuration']);
    expect($translation->headings[3])->toBe(['level' => 3, 'text' => 'Option A', 'anchor' => 'option-a']);
    expect($translation->headings[4])->toBe(['level' => 3, 'text' => 'Option B', 'anchor' => 'option-b']);
    expect($translation->headings[5])->toBe(['level' => 2, 'text' => 'Usage', 'anchor' => 'usage']);
});

it('stores empty array when content has no headings', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'No Headings',
        'slug' => 'no-headings',
        'content' => 'Just a plain paragraph with no headings.',
    ]);

    expect($translation->headings)->toBeArray();
    expect($translation->headings)->toBeEmpty();
});

it('stores null headings when content is null', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Null Content',
        'slug' => 'null-content',
        'content' => null,
    ]);

    $translation->refresh();

    expect($translation->headings)->toBeNull();
});

it('re-extracts headings when content is updated', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Changing',
        'slug' => 'changing',
        'content' => '## First Heading',
    ]);

    expect($translation->headings)->toHaveCount(1);
    expect($translation->headings[0]['text'])->toBe('First Heading');

    $translation->update(['content' => '## Updated Heading' . "\n\n" . '### Sub Heading']);

    $translation->refresh();

    expect($translation->headings)->toHaveCount(2);
    expect($translation->headings[0]['text'])->toBe('Updated Heading');
    expect($translation->headings[1]['text'])->toBe('Sub Heading');
});
