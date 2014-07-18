<?php
namespace Arrounded\Abstracts;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * Register the AbstractServiceProvider classes
 */
abstract class AbstractServiceProvider extends ServiceProvider
{
	/**
	 * The application's namespace
	 *
	 * @type string
	 */
	protected $namespace;

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// REGISTER //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Register various view composers
	 *
	 * @param array $composers
	 */
	protected function registerViewComposers(array $composers)
	{
		foreach ($composers as $composer => $views) {
			$composer = sprintf('%s\Composers\%s', $this->namespace, $composer);
			$this->app['view']->composer($views, $composer);
		}
	}

	//////////////////////////////////////////////////////////////////////
	//////////////////////////////// BOOT ////////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Register the bindings for repositories
	 */
	protected function bootRouteBindings()
	{
		// List all repositories
		$repositories = app_path($this->namespace.'/Repositories');
		$finder       = new Finder();
		$files        = $finder->in($repositories)->files();

		/** @type \SplFileObject $file */
		foreach ($files as $file) {
			// Create instance of repository
			$repository = sprintf('%s\Repositories\%s', $this->namespace, $file->getBasename('.php'));
			$repository = $this->app->make($repository);

			// Compute bindings
			$model    = $repository->getModel();
			$model    = Str::snake($model);
			$bindings = array_map('strtolower', array(
				$model,
				Str::plural($model),
			));

			// Register with router
			foreach ($bindings as $binding) {
				$this->app['router']->bind($binding, get_class($repository).'@find');
			}
		}
	}

	/**
	 * Boot a list of model observers
	 *
	 * @param array $observers
	 */
	protected function bootModelObserver(array $observers)
	{
		foreach ($observers as $observer) {
			$instance = sprintf('%s\Observers\%sObserver', $this->namespace, $observer);
			$instance = $this->app->make($instance);
			$observer::observe($instance);
		}
	}
}
