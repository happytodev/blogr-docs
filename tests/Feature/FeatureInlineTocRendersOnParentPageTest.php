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

    $this->child1 = DocArticle::create([
        'parent_id' => $this->parent->id,
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $this->child1->id,
        'locale' => 'en',
        'title' => 'Child One',
        'slug' => 'child-one',
        'content' => '# Child One' . "\n\n" . '## Setup' . "\n\n" . '### Requirements',
    ]);

    $this->child2 = DocArticle::create([
        'parent_id' => $this->parent->id,
        'position' => 1,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $this->child2->id,
        'locale' => 'en',
        'title' => 'Child Two',
        'slug' => 'child-two',
        'content' => '# Child Two' . "\n\n" . '## Configuration' . "\n\n" . '### Option A' . "\n\n" . '### Option B',
    ]);
});

it('renders inline TOC on parent doc page', function () {
    $response = $this->get('/docs/parent');

    $response->assertStatus(200);
    $response->assertSee('In this section');
    $response->assertSee('Child One');
    $response->assertSee('Child Two');
});

it('renders descendant headings in inline TOC', function () {
    $response = $this->get('/docs/parent');

    $response->assertStatus(200);
    $response->assertSee('Setup');
    $response->assertSee('Requirements');
    $response->assertSee('Configuration');
    $response->assertSee('Option A');
    $response->assertSee('Option B');
});

it('links descendant headings to their child page anchors', function () {
    $response = $this->get('/docs/parent');

    $response->assertStatus(200);
    $response->assertSee('/docs/parent/child-one');
    $response->assertSee('/docs/parent/child-two');
});

it('does not render inline TOC on leaf doc page', function () {
    $response = $this->get('/docs/parent/child-one');

    $response->assertStatus(200);
    $response->assertDontSee('In this section');
});

it('renders descendant tree with unlimited depth for grandchild pages', function () {
    $grandchild = DocArticle::create([
        'parent_id' => $this->child1->id,
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $grandchild->id,
        'locale' => 'en',
        'title' => 'Grandchild',
        'slug' => 'grandchild',
        'content' => '# Grandchild' . "\n\n" . '## Details',
    ]);

    $response = $this->get('/docs/parent');

    $response->assertStatus(200);
    $response->assertSee('Grandchild');
    $response->assertSee('Details');
});
