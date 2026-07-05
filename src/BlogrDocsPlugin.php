<?php

namespace Happytodev\BlogrDocs;

use Filament\Contracts\Plugin as FilamentPlugin;
use Filament\Panel;
use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;
use Happytodev\BlogrDocs\Filament\Pages\DocsSettings;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Filament\Resources\DocLearningPathResource;
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
        //
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
