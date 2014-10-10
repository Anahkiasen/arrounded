<?php
namespace Arrounded;

use Arrounded\Assets\AssetsHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Register the ArroundedServiceProvider classes
 */
class ArroundedServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('arrounded', 'arrounded', __DIR__.'/..');

		$this->app->singleton('arrounded.meta', 'Arrounded\Services\Metadata');
		$this->app->singleton('arrounded', 'Arrounded\Arrounded');

		$this->registerAssets();
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// This is needed to make sure the original HTML class
		// doesn't replace Arrounded's
		$this->app['html'];

		$this->app->singleton('html', 'Arrounded\Macros\HtmlBuilder');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
	 */
	public function provides()
	{
		return array('html', 'arrounded.meta');
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// BINDINGS //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Register the assets handler
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
