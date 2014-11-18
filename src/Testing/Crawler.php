<?php
namespace Arrounded\Testing;

use Arrounded\Abstracts\Models\AbstractModel;
use Arrounded\Arrounded;
use Arrounded\Traits\UsesContainer;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\Client;
use Illuminate\Support\Str;
use SplFileInfo;

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
	 * @type array
	 */
	protected $ignored = array();

	/**
	 * Lifetime of the cache
	 *
	 * @type integer
	 */
	protected $lifetime = 60;

	/**
	 * The number of entries to take for models
	 *
	 * @type integer|null
	 */
	protected $take = null;

	/**
	 * A cache of entries
	 *
	 * @type array
	 */
	protected $entries = [];

	/**
	 * @type boolean
	 */
	protected $ignoreIncomplete = false;

	/**
	 * @param Container $app
	 * @param Arrounded $arrounded
	 */
	public function __construct(Container $app, Arrounded $arrounded)
	{
		$this->app       = $app;
		$this->arrounded = $arrounded;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// CRAWLING //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the routes to test
	 *
	 * @param array $additional
	 *
	 * @return array
	 */
	public function getRoutes(array $additional = array())
	{
		$getRoutes = function () {
			$routes = array();

			foreach ($this->app['router']->getRoutes() as $route) {
				$method = method_exists($route, 'getMethods') ? $route->getMethods() : $route->methods();
				$method = array_get($method, 0);
				$uri    = method_exists($route, 'getPath') ? $route->getPath() : $route->uri();

				// Skip some routes
				if ($method != 'GET' || Str::contains($uri, $this->ignored)) {
					continue;
				}

				// Try regexes too
				foreach ($this->ignored as $ignored) {
					if (preg_match('#'.$ignored.'#', $uri)) {
						continue 2;
					}
				}

				// Replace models with their IDs
				if ($model = $this->extractModelFromUrl($uri)) {
					$entries = $this->fetchEntries($model);
					foreach ($entries as $model) {
						$model    = $this->replacePatternByKey($uri, $model);
						$routes[] = $this->app['url']->to($model);
					}
					continue;
				}

				if (strpos($uri, '{') !== false && $this->ignoreIncomplete) {
					continue;
				}

				$routes[] = $this->app['url']->to($uri);
			}

			return $routes;
		};

		// Cache the fetching of routes or not
		$routes = $this->cacheAndProcessRoutes($getRoutes);

		return array_merge($routes, $additional);
	}

	/**
	 * Get the routes in a PHPUnit-friendly forma
	 *
	 * @param array $additional
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

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// OPTIONS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * @param boolean $ignoreIncomplete
	 */
	public function setIgnoreIncomplete($ignoreIncomplete)
	{
		$this->ignoreIncomplete = $ignoreIncomplete;
	}

	/**
	 * @param array $entries
	 */
	public function setEntries($entries)
	{
		$this->entries = $entries;
	}

	/**
	 * @param int|null $entries
	 */
	public function setTake($entries)
	{
		$this->take = $entries;
	}

	/**
	 * Sets the Lifetime of the cache.
	 *
	 * @param integer $lifetime the lifetime
	 *
	 * @return self
	 */
	public function setLifetime($lifetime)
	{
		$this->lifetime = $lifetime;

		return $this;
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
	/////////////////////////////// CRAWLING ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a Client instance
	 *
	 * @return Client
	 */
	public function getClient()
	{
		return new Client($this->app, array());
	}

	/**
	 * Execute an action on all pages
	 *
	 * @param Closure $callback
	 *
	 * @return void
	 */
	public function onRoutes(Closure $callback)
	{
		$routes = $this->getRoutes();
		$client = $this->getClient();

		foreach ($routes as $route) {
			$callback($client, $route);
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fetch the entries for a model
	 *
	 * @param string $model
	 *
	 * @return array|Collection
	 */
	protected function fetchEntries($model)
	{
		if (!array_key_exists($model, $this->entries)) {
			$query = $model::query();

			if ($this->take) {
				$query = $query->take($this->take);
			}

			try {
				$this->entries[$model] = $query->get();
			} catch (\Exception $exception) {
				$this->entries[$model] = [];
			}
		}

		return $this->entries[$model];
	}

	/**
	 * Replace a model pattern by a key in an URL
	 *
	 * @param  string        $uri
	 * @param  AbstractModel $model
	 *
	 * @return string
	 */
	protected function replacePatternByKey($uri, AbstractModel $model)
	{
		return preg_replace('/\{([^}]+)\}/', $model->getIdentifier(), $uri);
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
		$model = str_replace('?', null, $model);
		if (!$model) {
			return;
		}

		$model = Str::singular($model);
		$model = $this->arrounded->qualifyModel($model);

		if (class_exists($model) && is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
			return $model;
		}
	}

	/**
	 * @param Closure $getRoutes
	 *
	 * @return array
	 */
	protected function cacheAndProcessRoutes(Closure $getRoutes)
	{
		if ($this->lifetime) {
			$mtime  = new SplFileInfo($this->app['path'].'/routes.php');
			$routes = $this->app['cache']->remember($mtime->getMTime(), $this->lifetime, $getRoutes);
		} else {
			$routes = $getRoutes();
		}

		return $routes;
	}
}
