<?php

namespace Happytodev\BlogrDocs;

use Happytodev\Blogr\Rendering\ShikiCodeBlockRenderer;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;
use Happytodev\BlogrDocs\Extensions\MediaEmbedAdapter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BlogrDocsServiceProvider extends PackageServiceProvider
{
    protected static string $viewNamespace = 'blogr-docs';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('blogr-docs')
            ->hasConfigFile('blogr-docs')
            ->hasMigrations([
                '2026_07_04_000001_create_doc_articles_table',
                '2026_07_04_000002_create_doc_article_translations_table',
                '2026_07_04_000003_create_doc_learning_paths_table',
                '2026_07_04_000004_create_doc_learning_path_translations_table',
                '2026_07_04_000005_create_doc_learning_path_article_table',
                '2026_07_04_000006_create_doc_article_drafts_table',
                '2026_07_04_000007_create_doc_article_versions_table',
            ])
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('blogr-docs.converter', function () {
            $environment = new Environment([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'embed' => [
                    'adapter' => new MediaEmbedAdapter(
                        config('blogr-docs.embeds', [])
                    ),
                    'allowed_domains' => [],
                    'fallback' => 'link',
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new EmbedExtension);
            $environment->addExtension(new TableExtension);

            $environment->addRenderer(FencedCode::class, new ShikiCodeBlockRenderer);
            $environment->addRenderer(IndentedCode::class, new ShikiCodeBlockRenderer);

            return new MarkdownConverter($environment);
        });
    }

    public function packageBooted(): void
    {
        $this->registerRoutes();
        $this->registerExtensions();
    }

    protected function registerRoutes(): void
    {
        if (! config('blogr-docs.enabled', true)) {
            return;
        }

        $prefix = config('blogr-docs.prefix', 'docs');
        $middleware = config('blogr-docs.middleware', ['web']);
        $router = $this->app['router'];
        $localesEnabled = config('blogr.locales.enabled', false);

        if ($localesEnabled) {
            $router->group([
                'middleware' => array_merge($middleware, [\Happytodev\Blogr\Http\Middleware\SetLocale::class]),
            ], function ($router) use ($prefix) {
                $router->get('/{locale}/'.$prefix, [
                    \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'index',
                ])->name('blogr-docs.index');

                $router->get('/{locale}/'.$prefix.'/{path}', [
                    \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'showLocalized',
                ])->where('path', '.*')->name('blogr-docs.show');

                $router->get('/{locale}/'.$prefix.'/{path}/pdf', [
                    \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'downloadPdfLocalized',
                ])->where('path', '.*')->name('blogr-docs.pdf');
            });
        }

            $router->group(['middleware' => $middleware], function ($router) use ($prefix) {
            $router->get($prefix, [
                \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'index',
            ])->name('blogr-docs.index');

            $router->get($prefix.'/{path}', [
                \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'show',
            ])->where('path', '.*')->name('blogr-docs.show');

            $router->get($prefix.'/{path}/pdf', [
                \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'downloadPdf',
            ])->where('path', '.*')->name('blogr-docs.pdf');
        });
    }

    protected function registerExtensions(): void
    {
        $this->app->booted(function () {
            if (! app()->bound(ExtensionRegistry::class)) {
                return;
            }

            $registry = app(ExtensionRegistry::class);

            $registry->register(new class implements \Happytodev\Blogr\Contracts\BlogrExtension
            {
                public function getId(): string { return 'blogr-docs'; }
                public function getName(): string { return 'Blogr Docs'; }
                public function getDescription(): string { return 'Hierarchical documentation system with learning paths, media embeds, and PDF export.'; }
                public function getVersion(): string { return '1.0.0'; }
                public function getAuthor(): string { return 'HappyToDev'; }
                public function getHomepage(): ?string { return null; }
                public function getDependencies(): array { return ['blogr-core']; }

                public function getSettingsUrl(): ?string
                {
                    return config('filament.path').'/'.config('blogr-docs.prefix', 'docs').'/settings';
                }

                public function registerExtension(ExtensionRegistry $registry): void {}
                public function registerLinkTypes(LinkTypeRegistry $registry): void {}
            });
        });
    }
}
