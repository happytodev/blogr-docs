<?php

namespace Happytodev\BlogrDocs\Tests;

use Happytodev\BlogrDocs\BlogrDocsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

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

        $app['config']->set('blogr-docs.enabled', true);
        $app['config']->set('blogr-docs.prefix', 'docs');
    }
}
