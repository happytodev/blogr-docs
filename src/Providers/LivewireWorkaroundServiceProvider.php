<?php

namespace Happytodev\BlogrDocs\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class LivewireWorkaroundServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(HandleRequests::class, function (HandleRequests $handler) {
            $handler->setUpdateRoute(function ($callback) {
                return $this->app['router']->post('/livewire/update', $callback)
                    ->middleware(['web'])
                    ->name('custom.livewire.update');
            });
        });
    }
}
