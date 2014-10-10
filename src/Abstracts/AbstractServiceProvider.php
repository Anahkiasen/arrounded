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

	/**
	 * Set the namespace to be used by Arrounded
	 *
	 * @param string|null $namespace
	 */
	protected function setNamespace($namespace = null)
	{
		$this->app['arrounded']->setNamespace($namespace ?: $this->namespace);
	}

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
		// Compute finding method
		$method = $this->app['request']->is('admin/*') ? 'findInTrash' : 'find';

		// List all repositories
		$repositories = app_path($this->namespace.'/Repositories');
		$finder       = new Finder();
		$files        = $finder->in($repositories)->files();

		/** @type \SplFileObject $file */
		foreach ($files as $file) {
			// Create instance of repository
			$basename   = $file->getBasename('.php');
			$repository = $this->app['arrounded']->getRepository($basename);

			// Compute bindings
			$model    = $repository->getModel();
			$model    = class_basename($model);
			$model    = Str::snake($model);
			$bindings = array_map('strtolower', array(
				$model,
				Str::plural($model),
			));

			// Register with router
			$repository = get_class($repository);
			foreach ($bindings as $binding) {
				$this->app['router']->bind($binding, $repository.'@'.$method);
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
			$instance = $this->app['arrounded']->getModelService($observer, 'Observer');
			$observer = $this->app['arrounded']->qualifyModel($observer);
			$observer::observe($instance);
		}
	}
}
