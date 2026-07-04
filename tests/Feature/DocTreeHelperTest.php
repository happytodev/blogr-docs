<?php

use Happytodev\BlogrDocs\Helpers\DocTreeHelper;
use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

beforeEach(function () {
    $this->helper = new DocTreeHelper;
});

it('builds empty tree when no articles exist', function () {
    $tree = $this->helper->buildTree('en');

    expect($tree)->toBeEmpty();
});

it('builds tree with root articles', function () {
    createPublishedArticle('sys', 'Systems');
    createPublishedArticle('dev', 'Development');

    $tree = $this->helper->buildTree('en');

    expect($tree)->toHaveCount(2);
    expect($tree[0]['translation']->title)->toBe('Systems');
    expect($tree[1]['translation']->title)->toBe('Development');
});

it('builds nested tree structure', function () {
    $sys = createPublishedArticle('sys', 'Systems');
    $linux = createPublishedArticle('linux', 'Linux', $sys->id);
    createPublishedArticle('bash', 'Bash Scripting', $linux->id);

    $tree = $this->helper->buildTree('en');

    expect($tree)->toHaveCount(1);
    expect($tree[0]['has_children'])->toBeTrue();
    expect($tree[0]['children'])->toHaveCount(1);
    expect($tree[0]['children'][0]['children'])->toHaveCount(1);
});

it('excludes unpublished articles from tree', function () {
    createPublishedArticle('pub', 'Published');
    createUnpublishedArticle('unpub', 'Unpublished');

    $tree = $this->helper->buildTree('en');

    expect($tree)->toHaveCount(1);
});

it('resolves single segment path', function () {
    createPublishedArticle('linux', 'Linux');

    $article = $this->helper->resolvePath(['linux'], 'en');

    expect($article)->not->toBeNull();
    expect($article->translation('en')->title)->toBe('Linux');
});

it('resolves multi-segment path', function () {
    $sys = createPublishedArticle('sys', 'Systems');
    createPublishedArticle('linux', 'Linux', $sys->id);

    $article = $this->helper->resolvePath(['sys', 'linux'], 'en');

    expect($article)->not->toBeNull();
    expect($article->translation('en')->title)->toBe('Linux');
    expect($article->parent_id)->toBe($sys->id);
});

it('returns null for unknown path', function () {
    $result = $this->helper->resolvePath(['unknown'], 'en');

    expect($result)->toBeNull();
});

it('provides previous and next siblings', function () {
    $parent = createPublishedArticle('section', 'Section');

    $first = DocArticle::create([
        'parent_id' => $parent->id, 'position' => 0,
        'is_published' => true, 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $first->id, 'locale' => 'en',
        'title' => 'First', 'slug' => 'first', 'content' => '# First',
    ]);

    $second = DocArticle::create([
        'parent_id' => $parent->id, 'position' => 1,
        'is_published' => true, 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $second->id, 'locale' => 'en',
        'title' => 'Second', 'slug' => 'second', 'content' => '# Second',
    ]);

    $third = DocArticle::create([
        'parent_id' => $parent->id, 'position' => 2,
        'is_published' => true, 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $third->id, 'locale' => 'en',
        'title' => 'Third', 'slug' => 'third', 'content' => '# Third',
    ]);

    expect($this->helper->getPrevArticle($first))->toBeNull();
    expect($this->helper->getNextArticle($first)->id)->toBe($second->id);

    expect($this->helper->getPrevArticle($second)->id)->toBe($first->id);
    expect($this->helper->getNextArticle($second)->id)->toBe($third->id);

    expect($this->helper->getPrevArticle($third)->id)->toBe($second->id);
    expect($this->helper->getNextArticle($third))->toBeNull();
});

// Helpers
function createPublishedArticle(string $slug, string $title, ?int $parentId = null): DocArticle
{
    $article = DocArticle::create([
        'parent_id' => $parentId,
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => $title,
        'slug' => $slug,
        'content' => '# '.$title,
    ]);

    return $article;
}

function createUnpublishedArticle(string $slug, string $title): DocArticle
{
    $article = DocArticle::create([
        'position' => 1,
        'is_published' => false,
        'default_locale' => 'en',
    ]);

    DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => $title,
        'slug' => $slug,
    ]);

    return $article;
}
