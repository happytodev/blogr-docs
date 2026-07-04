<?php

namespace Happytodev\BlogrDocs;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Happytodev\BlogrDocs\Filament\Pages\DocsSettings;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Filament\Resources\DocLearningPathResource;

class BlogrDocsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blogr-docs';
    }

    public function register(Panel $panel): void
    {
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
