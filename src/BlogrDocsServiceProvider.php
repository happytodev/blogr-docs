<?php

namespace Happytodev\BlogrDocs;

use Filament\PanelRegistry;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\BlogrDocs\Extensions\MediaEmbedAdapter;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Happytodev\BlogrDocs\Observers\DocArticleTranslationObserver;
use Happytodev\BlogrDocs\Filament\Pages\DocsSettings;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Filament\Resources\DocLearningPathResource;
use Happytodev\BlogrDocs\Filament\Resources\Pages\CreateDocArticle;
use Happytodev\BlogrDocs\Filament\Resources\Pages\CreateLearningPath;
use Happytodev\BlogrDocs\Filament\Resources\Pages\EditDocArticle;
use Happytodev\BlogrDocs\Filament\Resources\Pages\EditLearningPath;
use Happytodev\BlogrDocs\Filament\Resources\Pages\ListDocArticles;
use Happytodev\BlogrDocs\Filament\Resources\Pages\ListLearningPaths;
use Happytodev\Blogr\Rendering\Callout\CalloutExtension;
use Happytodev\Blogr\Rendering\ImageLightboxRenderer;
use Happytodev\Blogr\Rendering\ShikiCodeBlockRenderer;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Livewire\Livewire;
use Illuminate\Support\Str;
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
                '2026_07_04_000008_add_headings_to_doc_article_translations',
            ])
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageRegistered(): void
    {
        $this->registerPdfRoutes();
        $this->registerRoutes();

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

            if (class_exists(CalloutExtension::class)) {
                $environment->addExtension(new CalloutExtension);
            }

            if (class_exists(ShikiCodeBlockRenderer::class)) {
                $environment->addRenderer(FencedCode::class, new ShikiCodeBlockRenderer);
                $environment->addRenderer(IndentedCode::class, new ShikiCodeBlockRenderer);
            }

            $environment->addRenderer(Image::class, new ImageLightboxRenderer);

            return new MarkdownConverter($environment);
        });

        $this->app->singleton(BlogrDocsPlugin::class, fn () => new BlogrDocsPlugin);

        $this->app->afterResolving(PanelRegistry::class, function (PanelRegistry $registry): void {
            $panel = $registry->get('admin');

            if (! $panel) {
                return;
            }

            $panel->plugin($this->app->make(BlogrDocsPlugin::class));
        });
    }

    protected function registerPdfRoutes(): void
    {
        if (! config('blogr-docs.enabled', true)) {
            return;
        }

        $prefix = config('blogr-docs.prefix', 'docs');
        $middleware = config('blogr-docs.middleware', ['web']);
        $router = $this->app['router'];

        $router->get($prefix.'/{path}/pdf', [
            \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'downloadPdf',
        ])->where('path', '.*')->middleware($middleware)->name('blogr-docs.pdf');

        $router->get('/{locale}/'.$prefix.'/{path}/pdf', [
            \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'downloadPdfLocalized',
        ])->where('path', '.*')->middleware($middleware)->name('blogr-docs.pdf.localized');
    }

    public function packageBooted(): void
    {
        $this->ensureNodeInPath();
        $this->registerLivewireComponents();
        $this->registerExtensions();
        $this->registerObservers();
    }

    protected function ensureNodeInPath(): void
    {
        $nodeCandidates = ['/usr/bin', '/usr/local/bin', '/bin', '/opt/homebrew/bin'];
        $currentPath = getenv('PATH') ?: '';
        $needed = [];

        foreach ($nodeCandidates as $dir) {
            if (is_dir($dir) && ! str_contains($currentPath, $dir)) {
                $needed[] = $dir;
            }
        }

        if ($needed) {
            putenv('PATH=' . implode(PATH_SEPARATOR, $needed) . PATH_SEPARATOR . $currentPath);
        }
    }

    protected function registerLivewireComponents(): void
    {
        $components = [
            CreateDocArticle::class,
            EditDocArticle::class,
            ListDocArticles::class,
            CreateLearningPath::class,
            EditLearningPath::class,
            ListLearningPaths::class,
            DocsSettings::class,
        ];

        foreach ($components as $componentClass) {
            $componentName = Str::kebab(class_basename($componentClass));
            Livewire::component($componentName, $componentClass);
        }
    }

    protected function registerRoutes(): void
    {
        if (! config('blogr-docs.enabled', true)) {
            return;
        }

        $prefix = config('blogr-docs.prefix', 'docs');
        $middleware = config('blogr-docs.middleware', ['web']);
        $router = $this->app['router'];

        // Register localized routes
        $localesEnabled = config('blogr.locales.enabled', false);

        if ($localesEnabled) {
            $router->group([
                'middleware' => array_merge($middleware, [\Happytodev\Blogr\Http\Middleware\SetLocale::class]),
            ], function ($router) use ($prefix) {

                $router->get('/{locale}/'.$prefix, [
                    \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'index',
                ])->name('blogr-docs.index.localized');

                $router->get('/{locale}/'.$prefix.'/{path}', [
                    \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'showLocalized',
                ])->where('path', '.*')->name('blogr-docs.show.localized');
            });
        }

        $router->group(['middleware' => $middleware], function ($router) use ($prefix) {
            $router->get($prefix, [
                \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'index',
            ])->name('blogr-docs.index');

            $router->get($prefix.'/{path}', [
                \Happytodev\BlogrDocs\Http\Controllers\DocController::class, 'show',
            ])->where('path', '.*')->name('blogr-docs.show');
        });
    }

    protected function registerObservers(): void
    {
        DocArticleTranslation::observe(DocArticleTranslationObserver::class);
    }

    protected function registerExtensions(): void
    {
        if ($this->app->has(ExtensionRegistry::class)) {
            $registry = $this->app->make(ExtensionRegistry::class);
            $registry->register($this->app->make(BlogrDocsPlugin::class));
        }
    }
}
