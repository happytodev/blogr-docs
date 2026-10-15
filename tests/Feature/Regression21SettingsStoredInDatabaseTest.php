<?php

use Happytodev\BlogrDocs\BlogrDocsServiceProvider;
use Happytodev\BlogrDocs\Filament\Pages\DocsSettings;
use Happytodev\BlogrDocs\Services\SettingsRepository;
use Illuminate\Support\Facades\DB;

/**
 * Issue #21 — an admin save must live in the database, not in the config file.
 *
 * The page used to write the package's own config file (inside `vendor/`),
 * which any `composer install` replaces; the application's copy written by the
 * fallback is rebuilt from the repository at every deployment. Both lose the
 * save silently.
 */
beforeEach(function () {
    app(SettingsRepository::class)->flush();
});

it('regression_21_stores_an_admin_save_in_the_database_instead_of_the_config_file', function () {
    // The package's own config file: the first (and lossy) target of a save.
    $configFile = dirname(__DIR__, 2).'/config/blogr-docs.php';
    $before = md5_file($configFile);

    $page = new DocsSettings;
    $page->mount();
    $page->getSchema('form'); // a real request rebuilds the form before validating
    $page->prefix = 'documentation';
    $page->tocMaxLevel = 4;
    $page->save();

    // One row per setting, under its dotted configuration path.
    expect(DB::table(SettingsRepository::TABLE)->where('key', 'prefix')->value('value'))
        ->toBe('"documentation"')
        ->and(DB::table(SettingsRepository::TABLE)->where('key', 'toc.max_level')->value('value'))
        ->toBe('4');

    // And the values apply right away, so the page re-renders what was saved.
    expect(config('blogr-docs.prefix'))->toBe('documentation')
        ->and(config('blogr-docs.toc.max_level'))->toBe(4);

    // The config file is no longer rewritten.
    expect(md5_file($configFile))->toBe($before);
});

it('regression_21_applies_the_stored_settings_on_top_of_the_configuration', function () {
    config(['blogr-docs.prefix' => 'from-the-file']);
    config(['blogr-docs.sidebar.max_depth' => 5]);

    DB::table(SettingsRepository::TABLE)->insert([
        'key' => 'prefix',
        'value' => '"from-the-database"',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $repository = app(SettingsRepository::class);
    $repository->flush();
    $repository->applyToConfig();

    expect(config('blogr-docs.prefix'))->toBe('from-the-database')  // the table wins
        ->and(config('blogr-docs.sidebar.max_depth'))->toBe(5);     // the file keeps the rest
});

it('regression_21_applies_the_stored_settings_when_the_provider_boots', function () {
    app(SettingsRepository::class)->put(['prefix' => 'documentation']);
    config(['blogr-docs.prefix' => 'docs']); // the value from the file

    // The provider applies the stored settings at boot, before anything reads
    // the configuration: routes, converters, views.
    (new BlogrDocsServiceProvider(app()))->packageBooted();

    expect(config('blogr-docs.prefix'))->toBe('documentation');
});

it('regression_21_never_stores_a_null_value', function () {
    // A page saved without being mounted carries nulls (unset watermark image).
    $page = new DocsSettings;
    $page->getSchema('form');
    $page->save();

    $rows = DB::table(SettingsRepository::TABLE)->pluck('value', 'key');

    expect($rows->count())->toBeGreaterThan(0)              // the save did store something
        ->and($rows->has('pdf.watermark.image'))->toBeFalse() // but not the unset image
        ->and($rows->values())->not->toContain('null');
});

it('regression_21_does_not_bake_the_overrides_into_the_compiled_configuration', function () {
    DB::table(SettingsRepository::TABLE)->insert([
        'key' => 'prefix',
        'value' => '"from-the-database"',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config(['blogr-docs.prefix' => 'from-the-file']);

    // `php artisan config:cache` boots the application before freezing the
    // configuration: applying the overrides there would bake them into
    // `bootstrap/cache/config.php`, which would then outrank the table.
    $_SERVER['argv'] = ['artisan', 'config:cache'];

    try {
        app(SettingsRepository::class)->applyToConfig();
    } finally {
        unset($_SERVER['argv']);
    }

    expect(config('blogr-docs.prefix'))->toBe('from-the-file');
});

it('regression_21_stops_applying_a_setting_once_it_is_deleted', function () {
    // The stored list is never cached: a stale entry would keep a deleted
    // setting alive for the rest of the request.
    $repository = app(SettingsRepository::class);
    $repository->put(['prefix' => 'documentation']);
    $repository->applyToConfig();

    expect(config('blogr-docs.prefix'))->toBe('documentation');

    $repository->forgetAll();
    config(['blogr-docs.prefix' => 'docs']);
    app(SettingsRepository::class)->applyToConfig();

    expect(config('blogr-docs.prefix'))->toBe('docs');
});

it('regression_21_forgets_the_stored_settings_on_reset', function () {
    $repository = app(SettingsRepository::class);
    $repository->put(['prefix' => 'documentation', 'toc.max_level' => 4]);

    expect($repository->count())->toBe(2)
        ->and($repository->forgetAll())->toBe(2)
        ->and(DB::table(SettingsRepository::TABLE)->count())->toBe(0)
        ->and(app(SettingsRepository::class)->all())->toBe([]);
});

it('regression_21_falls_back_to_the_config_file_while_the_table_is_missing', function () {
    $configFile = dirname(__DIR__, 2).'/config/blogr-docs.php';
    $before = md5_file($configFile);

    app()->instance(SettingsRepository::class, new class extends SettingsRepository
    {
        public function hasTable(): bool
        {
            return false;
        }
    });

    $page = new DocsSettings;
    $page->mount();
    $page->getSchema('form');
    $page->prefix = 'documentation';

    $page->save();

    // In the test environment the fallback applies the values in memory instead
    // of rewriting the config file, and stores nothing.
    expect(config('blogr-docs.prefix'))->toBe('documentation')
        ->and(DB::table(SettingsRepository::TABLE)->count())->toBe(0)
        ->and(md5_file($configFile))->toBe($before);
});
