<?php namespace Mwakisha\Tarablade;

use Illuminate\Support\ServiceProvider;

class TarabladeServiceProvider extends ServiceProvider 
{
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() 
    {
        $configPath = __DIR__ . '/../config/tarablade.php';
        $this->mergeConfigFrom($configPath, 'tarablade');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/tarablade.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('tarablade.php');
    }
}