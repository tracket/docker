<?php

namespace Tracket\Docker;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Tracket\Docker\Console\InstallCommand;
use Tracket\Docker\Console\PublishCommand;

class DockerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
        $this->configurePublishing();
    }

    /**
     * Register the console commands for the package.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
            ]);
        }
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../images' => $this->app->basePath('docker'),
            ], ['tracket-docker']);

            $this->publishes([
                __DIR__ . '/../bin' => $this->app->basePath('bin'),
            ], ['tracket-bin']);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            InstallCommand::class,
            PublishCommand::class,
        ];
    }
}