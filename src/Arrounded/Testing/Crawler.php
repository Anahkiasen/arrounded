<?php
namespace Arrounded\Testing;

use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

/**
 * A basic class to extract routes
 * from the application and crawler them
 */
class Crawler
{
	use UsesContainer;

	/**
	 * The routes to ignore
	 *
	 * @var array
	 */
	protected $ignored = array();

	/**
	 * Get the routes to test
	 *
	 * @return array
	 */
	public function getRoutes(array $additional = array())
	{
		$routes = $this->app['cache']->remember('crawler-routes', 60, function () {
			$routes = array();

			foreach ($this->app['router']->getRoutes() as $route) {
				$method = $route->getMethods()[0];
				$uri    = $route->getPath();

				// Skip some routes
				if ($method != 'GET' or Str::contains($uri, $this->ignored)) {
					continue;
				}

				// Replace models with their IDs
				if ($model = $this->extractModelFromUrl($uri)) {
					foreach ($model::take(3)->get() as $model) {
						$model    = $this->replacePatternByKey($uri, $model->id);
						$routes[] = $this->app['url']->to($model);
					}
					continue;
				}

				$routes[] = $this->app['url']->to($uri);
			}

			return $routes;
		});

		return array_merge($routes, $additional);
	}

	/**
	 * Get the routes in a PHPUnit-friendly forma
	 *
	 * @return array
	 */
	public function provideRoutes(array $additional = array())
	{
		$queue  = array();
		$routes = $this->getRoutes($additional);

		// Build provider
		foreach ($routes as $route) {
			$queue[] = [$route];
		}

		return $queue;
	}

	/**
	 * Set the routes to ignore
	 *
	 * @param array $ignored
	 */
	public function setIgnored(array $ignored = array())
	{
		$this->ignored = $ignored;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Replace a model pattern by a key in an URL
	 *
	 * @param  string  $uri
	 * @param  integer $key
	 *
	 * @return string
	 */
	protected function replacePatternByKey($uri, $key)
	{
		return preg_replace('/\{([^}]+)\}/', $key, $uri);
	}

	/**
	 * Extract a model pattern in an URL
	 *
	 * @param  string $url
	 *
	 * @return string|false
	 */
	protected function extractModelFromUrl($url)
	{
		// Extract model
		preg_match('/\{([^}]+)\}/', $url, $pattern);
		$model = Str::studly(array_get($pattern, 1));
		$model = Str::singular($model);

		if (class_exists($model) and is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
			return $model;
		}

		return false;
	}
}