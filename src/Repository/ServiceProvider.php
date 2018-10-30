<?php 

namespace Dees040\Repository;

use Dees040\Repository\Commands\RepositoryMakeCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

    /**
     * This will be used to register config & view in your package namespace.
     *
     * @var  string
     */
    protected $packageName = 'repository';

    /**
     * A list of artisan commands for your package
     * 
     * @var array
     */
    protected $commands = [
        RepositoryMakeCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/../lang', $this->packageName);
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/'. $this->packageName),
        ]);

        // Publish your config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path($this->packageName.'.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', $this->packageName
        );
    }
}
