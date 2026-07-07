<?php

use Happytodev\Blogr\Services\LinkTypeRegistry;
use Happytodev\BlogrDocs\BlogrDocsPlugin;
use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

it('registers a docs link type', function () {
    $registry = new LinkTypeRegistry;
    $plugin = app(BlogrDocsPlugin::class);

    $plugin->registerLinkTypes($registry);

    expect($registry->has('docs'))->toBeTrue();
    expect($registry->getOptions())->toHaveKey('docs');
    expect($registry->getOptions()['docs'])->toBe('Docs');
});

it('resolves to docs index when no article id is provided', function () {
    $registry = new LinkTypeRegistry;
    $plugin = app(BlogrDocsPlugin::class);
    $plugin->registerLinkTypes($registry);

    $url = $registry->resolve('docs', []);

    expect($url)->not->toBeNull();
    expect($url)->toContain('/docs');
});

it('resolves to specific article url when article id is provided', function () {
    $article = DocArticle::create([
        'position' => 0,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Installation Guide',
        'slug' => 'installation',
    ]);

    $registry = new LinkTypeRegistry;
    $plugin = app(BlogrDocsPlugin::class);
    $plugin->registerLinkTypes($registry);

    $url = $registry->resolve('docs', ['doc_article_id' => $article->id]);

    expect($url)->not->toBeNull();
    expect($url)->toContain('/docs/installation');
});

it('returns null when article id does not exist', function () {
    $registry = new LinkTypeRegistry;
    $plugin = app(BlogrDocsPlugin::class);
    $plugin->registerLinkTypes($registry);

    $url = $registry->resolve('docs', ['doc_article_id' => 999]);

    expect($url)->toBeNull();
});

it('provides a field factory that returns a Select component', function () {
    $registry = new LinkTypeRegistry;
    $plugin = app(BlogrDocsPlugin::class);
    $plugin->registerLinkTypes($registry);

    $factories = $registry->getFieldFactories();

    expect($factories)->toHaveKey('docs');

    $field = $factories['docs']();

    expect($field)->toBeInstanceOf(\Filament\Forms\Components\Select::class);
    expect($field->getName())->toBe('doc_article_id');
});
