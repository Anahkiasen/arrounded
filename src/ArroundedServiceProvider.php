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

		$this->app->singleton('Arrounded\Assets\AssetsHandler', function ($app) {
			return new AssetsHandler($app['config']['assets']);
		});

		$this->app->bind('arrounded.assets.replacer', 'Arrounded\Assets\AssetsReplacer');

		$this->commands(['arrounded.assets.replacer']);
	}
}
