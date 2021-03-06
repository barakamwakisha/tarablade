<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\ServiceProvider;

class TarabladeServiceProvider extends ServiceProvider
{
    // @codeCoverageIgnoreStart

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tarablade.php', 'tarablade');
        $this->commands([
            Console\ImportCommand::class,
        ]);
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/tarablade.php' => config_path('tarablade.php'),
        ], 'tarablade-config');
    }

    // @codeCoverageIgnoreEnd
}
