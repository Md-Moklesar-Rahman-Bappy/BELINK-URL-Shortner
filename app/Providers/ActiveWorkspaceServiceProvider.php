<?php

namespace App\Providers;

use App\ActiveWorkspace;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ActiveWorkspaceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ActiveWorkspace::class, function (Application $app) {
            return new ActiveWorkspace();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ActiveWorkspace::class];
    }
}
