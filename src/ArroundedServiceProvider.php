<?php
namespace Arrounded;

use Arrounded\Assets\AssetsHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Register the ArroundedServiceProvider classes.
 */
class ArroundedServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('arrounded.meta', 'Arrounded\Services\Metadata');

        $this->app->singleton('Arrounded\Arrounded', 'Arrounded\Arrounded');
        $this->app->alias('Arrounded\Arrounded', 'arrounded');

        $this->registerAssets();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['arrounded.meta'];
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// BINDINGS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Register the assets handler.
     */
    protected function registerAssets()
    {
        $this->app->singleton('Arrounded\Assets\AssetsHandler', function ($app) {
            return new AssetsHandler($app['config']['assets']);
        });

        $this->app->bind('arrounded.assets.replacer', 'Arrounded\Assets\AssetsReplacer');

        $this->commands(['arrounded.assets.replacer']);
    }
}
