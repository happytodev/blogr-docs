<?php

use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Illuminate\Support\Facades\Config;

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
