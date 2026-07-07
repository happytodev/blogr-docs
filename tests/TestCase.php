<?php

namespace Happytodev\BlogrDocs\Tests;

use Happytodev\BlogrDocs\BlogrDocsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createViteManifest();

        $this->app['view']->addNamespace('blogr', [
            __DIR__.'/../vendor/happytodev/blogr/resources/views',
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Barryvdh\DomPDF\ServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            BlogrDocsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:m9mYgh2CW61rjelALqBbuXZeCBqk9TqHMnt0VOg9uJA=');
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.url', 'http://localhost');

        $app['config']->set('blogr.locales.available', ['en', 'fr']);
        $app['config']->set('blogr.locales.default', 'en');
        $app['config']->set('blogr.locales.enabled', false);

        $app['config']->set('blogr-docs.enabled', true);
        $app['config']->set('blogr-docs.prefix', 'docs');

        $app['config']->set('livewire.class_namespace', 'App\\Livewire');
    }

    protected function createViteManifest(): void
    {
        $manifestPath = __DIR__.'/../vendor/orchestra/testbench-core/laravel/public/build/manifest.json';

        if (! is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0755, true);
        }

        if (! file_exists($manifestPath)) {
            file_put_contents($manifestPath, json_encode([
                'resources/css/app.css' => [
                    'file' => 'assets/app.css',
                    'src' => 'resources/css/app.css',
                ],
                'resources/js/app.js' => [
                    'file' => 'assets/app.js',
                    'src' => 'resources/js/app.js',
                    'isEntry' => true,
                ],
            ]));
        }
    }

    protected function defineRoutes($router): void
    {
        $router->get('/blog/feed', function () {
            return response('<?xml version="1.0"?><rss><channel><title>Test</title></channel></rss>');
        })->name('blog.feed');
    }
}
