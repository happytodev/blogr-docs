<?php

namespace Happytodev\BlogrDocs;

use Filament\Contracts\Plugin as FilamentPlugin;
use Filament\Forms\Components\Select;
use Filament\Panel;
use Filament\Schemas\Components\Utilities\Get;
use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;
use Happytodev\BlogrDocs\Filament\Pages\DocsSettings;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Filament\Resources\DocLearningPathResource;
use Happytodev\BlogrDocs\Models\DocArticle;
use Illuminate\Support\Facades\DB;

class BlogrDocsPlugin implements BlogrExtension, FilamentPlugin
{

    public function getId(): string
    {
        return 'blogr-docs';
    }

    public function getName(): string
    {
        return 'Blogr Docs';
    }

    public function getDescription(): string
    {
        return 'Hierarchical documentation system with learning paths, media embeds, and PDF export.';
    }

    public function getVersion(): string
    {
        return Blogr::VERSION;
    }

    public function getAuthor(): string
    {
        return 'HappyToDev';
    }

    public function getHomepage(): ?string
    {
        return 'https://github.com/happytodev/blogr-docs';
    }

    public function getDependencies(): array
    {
        return ['blogr-core'];
    }

    public function getSettingsUrl(): ?string
    {
        try {
            $disabled = DB::table('blogr_extension_states')
                ->where('extension_id', 'blogr-docs')
                ->whereNotNull('disabled_at')
                ->exists();
        } catch (\Throwable) {
            $disabled = false;
        }

        if ($disabled) {
            return null;
        }

        try {
            return DocsSettings::getUrl();
        } catch (\Throwable) {
            return null;
        }
    }

    public function register(Panel $panel): void
    {
        try {
            $disabled = DB::table('blogr_extension_states')
                ->where('extension_id', 'blogr-docs')
                ->whereNotNull('disabled_at')
                ->exists();
        } catch (\Throwable) {
            $disabled = false;
        }

        if ($disabled) {
            return;
        }

        $panel->resources([
            DocArticleResource::class,
            DocLearningPathResource::class,
        ]);

        $panel->pages([
            DocsSettings::class,
        ]);

        $panel->navigationGroups([
            'Docs',
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function registerExtension(ExtensionRegistry $registry): void
    {
        //
    }

    public function registerLinkTypes(LinkTypeRegistry $registry): void
    {
        $registry->register('docs', 'Docs', function (array $context = []) {
            $articleId = $context['doc_article_id'] ?? null;

            if ($articleId) {
                $article = DocArticle::with('translations')->find($articleId);

                if (! $article) {
                    return null;
                }

                $locale = app()->getLocale();
                $translation = $article->translation($locale) ?? $article->defaultTranslation();

                if (! $translation) {
                    return null;
                }

                return $translation->url();
            }

            $localesEnabled = config('blogr.locales.enabled', false);

            try {
                if ($localesEnabled) {
                    return route('blogr-docs.index.localized', ['locale' => app()->getLocale()]);
                }

                return route('blogr-docs.index');
            } catch (\Throwable) {
                $prefix = config('blogr-docs.prefix', 'docs');

                if ($localesEnabled) {
                    return '/'.app()->getLocale().'/'.$prefix;
                }

                return '/'.$prefix;
            }
        }, function () {
            return Select::make('doc_article_id')
                ->label('Select Doc Article')
                ->options(function () {
                    return DocArticle::with('translations')
                        ->get()
                        ->mapWithKeys(function (DocArticle $article) {
                            $translation = $article->translations->first();

                            return [$article->id => $translation->title ?? 'Article #'.$article->id];
                        });
                })
                ->searchable()
                ->placeholder('Docs homepage (no specific article)')
                ->visible(fn (Get $get) => $get('type') === 'docs')
                ->columnSpan(1);
        });
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
