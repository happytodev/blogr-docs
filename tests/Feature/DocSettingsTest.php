<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    if (! Route::has('blog.feed')) {
        Route::get('/feed', fn () => 'feed')->name('blog.feed');
    }
    if (! Route::has('blog.index')) {
        Route::get('/blog', fn () => 'blog')->name('blog.index');
    }
});

it('reads config with custom values', function () {
    Config::set('blogr-docs.enabled', true);
    Config::set('blogr-docs.prefix', 'docs');
    Config::set('blogr-docs.pdf.enabled', false);
    Config::set('blogr-docs.pdf.driver', 'dompdf');
    Config::set('blogr-docs.pdf.page_size', 'A4');
    Config::set('blogr-docs.pdf.orientation', 'portrait');
    Config::set('blogr-docs.embeds.youtube', true);
    Config::set('blogr-docs.embeds.spotify', true);
    Config::set('blogr-docs.embeds.soundcloud', true);

    expect(config('blogr-docs.enabled'))->toBeTrue();
    expect(config('blogr-docs.prefix'))->toBe('docs');
    expect(config('blogr-docs.pdf.enabled'))->toBeFalse();
    expect(config('blogr-docs.pdf.driver'))->toBe('dompdf');
    expect(config('blogr-docs.pdf.page_size'))->toBe('A4');
    expect(config('blogr-docs.pdf.orientation'))->toBe('portrait');
    expect(config('blogr-docs.embeds.youtube'))->toBeTrue();
    expect(config('blogr-docs.embeds.spotify'))->toBeTrue();
    expect(config('blogr-docs.embeds.soundcloud'))->toBeTrue();
});

it('returns 404 on pdf download when pdf is disabled', function () {
    Config::set('blogr-docs.pdf.enabled', false);

    $article = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'Test Article', 'slug' => 'test-article',
        'content' => '# Hello',
    ]);

    $response = $this->get('/docs/test-article/pdf');
    $response->assertStatus(404);
});

it('generates pdf content for enabled articles', function () {
    Config::set('blogr-docs.pdf.enabled', true);

    $article = DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
    ]);
    DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'PDF Test', 'slug' => 'pdf-test',
        'content' => '# PDF Content',
    ]);

    try {
        $response = $this->get('/docs/pdf-test/pdf');
        $response->assertStatus(200);
    } catch (\Exception $e) {
        if (str_contains($e->getMessage(), 'dompdf') || str_contains($e->getMessage(), 'DomPDF')) {
            $this->markTestSkipped('DomPDF not configured in test environment');
        } else {
            throw $e;
        }
    }
});

it('returns 404 for unknown article pdf', function () {
    Config::set('blogr-docs.pdf.enabled', true);

    $response = $this->get('/docs/nonexistent/pdf');
    $response->assertStatus(404);
});

it('registers docs routes', function () {
    $routes = collect(app('router')->getRoutes()->getRoutesByName());

    expect($routes->has('blogr-docs.index'))->toBeTrue();
    expect($routes->has('blogr-docs.show'))->toBeTrue();
    expect($routes->has('blogr-docs.pdf'))->toBeTrue();
});

it('has correct plugin id', function () {
    $plugin = app(\Happytodev\BlogrDocs\BlogrDocsPlugin::class);

    expect($plugin->getId())->toBe('blogr-docs');
});

it('has correct version constant', function () {
    expect(\Happytodev\BlogrDocs\Blogr::VERSION)->toMatch('/^\d+\.\d+\.\d+$/');
});

test('extension version matches Blogr::VERSION', function () {
    $extension = new class implements \Happytodev\Blogr\Contracts\BlogrExtension
    {
        public function getId(): string { return 'blogr-docs'; }
        public function getName(): string { return 'Blogr Docs'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return \Happytodev\BlogrDocs\Blogr::VERSION; }
        public function getAuthor(): string { return 'HappyToDev'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return ['blogr-core']; }
        public function getSettingsUrl(): ?string { return null; }
        public function registerExtension(\Happytodev\Blogr\Services\ExtensionRegistry $registry): void {}
        public function registerLinkTypes(\Happytodev\Blogr\Services\LinkTypeRegistry $registry): void {}
    };

    expect($extension->getVersion())->toBe(\Happytodev\BlogrDocs\Blogr::VERSION);
});

test('callout blocks are rendered from Markdown', function () {
    $converter = app('blogr-docs.converter');
    $html = $converter->convert(":::tip[How to use]\n\nContent here.\n\nMore content.\n\n:::")->getContent();

    expect($html)->toContain('class="docs-callout docs-callout--tip"');
    expect($html)->toContain('How to use');
    expect($html)->toContain('Content here.');
    expect($html)->toContain('</aside>');
});

test('callout without title renders correctly', function () {
    $converter = app('blogr-docs.converter');
    $html = $converter->convert(":::info\n\nJust info.\n\n:::")->getContent();

    expect($html)->toContain('class="docs-callout docs-callout--info"');
    expect($html)->toContain('Just info.');
});

test('multiple callout types are supported', function () {
    $converter = app('blogr-docs.converter');

    $html = $converter->convert(":::danger[Danger zone]\n\nBeware!\n\n:::")->getContent();
    expect($html)->toContain('class="docs-callout docs-callout--danger"');
    expect($html)->toContain('Danger zone');
    expect($html)->toContain('Beware!');

    $html = $converter->convert(":::caution[Careful]\n\nWatch out.\n\n:::")->getContent();
    expect($html)->toContain('class="docs-callout docs-callout--caution"');
    expect($html)->toContain('Careful');
    expect($html)->toContain('Watch out.');
});

test('all page components can be instantiated', function () {
    $pages = [
        \Happytodev\BlogrDocs\Filament\Resources\Pages\CreateDocArticle::class,
        \Happytodev\BlogrDocs\Filament\Resources\Pages\EditDocArticle::class,
        \Happytodev\BlogrDocs\Filament\Resources\Pages\ListDocArticles::class,
        \Happytodev\BlogrDocs\Filament\Resources\Pages\CreateLearningPath::class,
        \Happytodev\BlogrDocs\Filament\Resources\Pages\EditLearningPath::class,
        \Happytodev\BlogrDocs\Filament\Resources\Pages\ListLearningPaths::class,
        \Happytodev\BlogrDocs\Filament\Pages\DocsSettings::class,
    ];

    foreach ($pages as $page) {
        expect(fn () => app($page))->not->toThrow(\Throwable::class);
    }
});

test('toc is rendered when display_toc is enabled', function () {
    Config::set('blogr-docs.toc.enabled', true);

    $article = \Happytodev\BlogrDocs\Models\DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
        'display_toc' => true,
    ]);
    \Happytodev\BlogrDocs\Models\DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'TOC Test', 'slug' => 'toc-test',
        'content' => "# Title\n\n## Introduction\n\nContent here.\n\n## Details\n\nMore content.\n\n### Sub detail\n\nDeep content.",
    ]);

    $response = $this->get('/docs/toc-test');
    $response->assertStatus(200);

    $response->assertSee('<ul class="toc-list', false);
});

test('toc is hidden when display_toc is disabled', function () {
    Config::set('blogr-docs.toc.enabled', true);

    $article = \Happytodev\BlogrDocs\Models\DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
        'display_toc' => false,
    ]);
    \Happytodev\BlogrDocs\Models\DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'No TOC', 'slug' => 'no-toc',
        'content' => "# Title\n\n## Heading\n\nContent.",
    ]);

    $response = $this->get('/docs/no-toc');
    $response->assertStatus(200);

    $response->assertDontSee('toc-list', false);
});

test('pdf template includes callout css styling for tip/info/danger/caution blocks', function () {
    Config::set('blogr-docs.pdf.enabled', true);

    $html = view('blogr-docs::pdf', [
        'title' => 'Test Article',
        'content' => '<aside class="docs-callout docs-callout--tip">callout</aside>',
        'locale' => 'en',
        'seoTitle' => null,
        'seoDescription' => null,
    ])->render();

    expect($html)->toContain('.docs-callout');
    expect($html)->toContain('.docs-callout--tip');
    expect($html)->toContain('.docs-callout--info');
    expect($html)->toContain('.docs-callout--danger');
    expect($html)->toContain('.docs-callout--caution');
    expect($html)->toContain('.docs-callout__title');
    expect($html)->toContain('.docs-callout__content');
});

test('heading IDs are always injected regardless of display_toc', function () {
    Config::set('blogr-docs.toc.enabled', true);

    $article = \Happytodev\BlogrDocs\Models\DocArticle::create([
        'position' => 0, 'is_published' => true,
        'published_at' => now(), 'default_locale' => 'en',
        'display_toc' => false,
    ]);
    \Happytodev\BlogrDocs\Models\DocArticleTranslation::create([
        'doc_article_id' => $article->id, 'locale' => 'en',
        'title' => 'Anchors', 'slug' => 'anchors',
        'content' => "## Introduction\n\nBody.",
    ]);

    $response = $this->get('/docs/anchors');
    $response->assertStatus(200);

    $response->assertSee('id="introduction"', false);
});
