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
 * from the application and crawler them.
 */
class Crawler
{
    use UsesContainer;

    /**
     * The found routes.
     *
     * @type array
     */
    protected $routes = [];

    /**
     * The routes to ignore.
     *
     * @type array
     */
    protected $ignored = [];

    /**
     * The allowed routes.
     *
     * @type array
     */
    protected $allowed = [];

    /**
     * Lifetime of the cache.
     *
     * @type int
     */
    protected $lifetime = 60;

    /**
     * The number of entries to take for models.
     *
     * @type int|null
     */
    protected $take = null;

    /**
     * A cache of entries.
     *
     * @type array
     */
    protected $entries = [];

    /**
     * The already registered routes.
     *
     * @type array
     */
    protected $registered = [];

    /**
     * @type bool
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
     * Get the routes to test.
     *
     * @param array $additional
     *
     * @return array
     */
    public function getRoutes(array $additional = [])
    {
        $getRoutes = function () {
            $existing = $this->app['router']->getRoutes();

            foreach ($existing as $route) {
                $method = method_exists($route, 'getMethods') ? $route->getMethods() : $route->methods();
                $method = array_get($method, 0);
                $uri    = method_exists($route, 'getPath') ? $route->getPath() : $route->uri();

                // Skip some routes
                if ($method != 'GET' || Str::contains($uri, $this->ignored)) {
                    continue;
                }

                // Skip if route is not allowed.
                if ( ! $this->isAllowed($uri)) {
                    continue;
                }

                // Skip already registered
                $action = $route->getActionName();
                if ($action !== 'Closure' && in_array($action, $this->registered)) {
                    continue;
                } else {
                    $this->registered[] = $action;
                }

                // Try regexes too
                foreach ($this->ignored as $ignored) {
                    if (preg_match('#'.$ignored.'#', $uri)) {
                        continue 2;
                    }
                }

                // Replace models with their IDs
                if ($patterns = $this->extractPatternsFromUrl($uri)) {
                    $this->processModelRoute($patterns, $uri, $action);
                    continue;
                }

                // If the route still has unreplaced patterns, ignore it
                if ($this->hasIncompletePatterns($uri)) {
                    continue;
                }

                $this->routes[] = $this->app['url']->to($uri);
            }
        };

        // Cache the fetching of routes or not
        $this->cacheAndProcessRoutes($getRoutes);

        return array_merge($this->routes, $additional);
    }

    /**
     * Get the routes in a PHPUnit-friendly forma.
     *
     * @param array $additional
     *
     * @return array
     */
    public function provideRoutes(array $additional = [])
    {
        $queue  = [];
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
     * @param bool $ignoreIncomplete
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
     * @param int $lifetime the lifetime
     *
     * @return self
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Set the routes to ignore.
     *
     * @param array $ignored
     */
    public function setIgnored(array $ignored = [])
    {
        $this->ignored = $ignored;
    }

    /**
     * Set the allowed routes.
     *
     * @param array $allowed
     */
    public function setAllowed(array $allowed = [])
    {
        $this->allowed = $allowed;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// CRAWLING ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get a Client instance.
     *
     * @return Client
     */
    public function getClient()
    {
        return new Client($this->app, []);
    }

    /**
     * Execute an action on all pages.
     *
     * @param Closure $callback
     */
    public function onRoutes(Closure $callback)
    {
        $routes = $this->getRoutes();
        $client = $this->getClient();

        foreach ($routes as $route) {
            $callback($client, $route);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// PATTERNS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $uri
     *
     * @return bool
     */
    protected function hasIncompletePatterns($uri)
    {
        return strpos($uri, '{') !== false && $this->ignoreIncomplete;
    }

    /**
     * Extract the various patterns in an URL.
     *
     * @param string $uri
     *
     * @return array
     */
    protected function extractPatternsFromUrl($uri)
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        $matches = array_get($matches, 1);

        // Keep patterns that match a model
        $patterns = [];
        foreach ($matches as $match) {
            if ($model = $this->patternHasModel($match)) {
                $patterns[$match] = $model;
            }
        }

        return $patterns;
    }

    /**
     * Extract a model pattern in an URL.
     *
     * @param string $pattern
     *
     * @return string|false
     */
    protected function patternHasModel($pattern)
    {
        // Extract model
        $model = str_replace('_slug', null, $pattern);
        $model = Str::studly($model);
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
     * Process a route involving models.
     *
     * @param array  $patterns
     * @param string $uri
     * @param string $action
     */
    protected function processModelRoute(array $patterns, $uri, $action)
    {
        // Compute the main model and fetch its entries
        list($mainPattern, $main) = $this->computeMainModelFromPatterns($patterns, $action);
        $entries                  = $this->fetchEntries($main);

        foreach ($entries as $model) {
            $replacedUri = $this->replacePatternWithModel($uri, $model);

            // Replace extraneous patterns
            if (count($patterns) > 1) {
                foreach ($patterns as $pattern => $related) {
                    $pattern = str_replace('_slug', null, $pattern);

                    if ($pattern !== $mainPattern && $model->$pattern) {
                        $replacedUri = $this->replacePatternWithModel($replacedUri, $model->$pattern);
                    }
                }
            }

            if (!$this->hasIncompletePatterns($replacedUri)) {
                $this->routes[] = $this->app['url']->to($replacedUri);
            }
        }
    }

    /**
     * If the route has multiple patterns, compute the mail one to use.
     *
     * @param string $patterns
     * @param string $action
     *
     * @return string
     */
    protected function computeMainModelFromPatterns($patterns, $action)
    {
        // If only one pattern, it's the main one
        if (count($patterns) == 1) {
            return [array_keys($patterns)[0], head($patterns)];
        }

        // Else look at the action and infer from that
        foreach ($patterns as $pattern => $model) {
            $basename = class_basename($model);
            if (Str::contains($action, $basename)) {
                return [$pattern, $model];
            }
        }
    }

    /**
     * Replace a model pattern by a key in an URL.
     *
     * @param string        $uri
     * @param AbstractModel $model
     *
     * @return string
     */
    protected function replacePatternWithModel($uri, AbstractModel $model)
    {
        // Compute pattern from model
        $pattern = $model->getClassBasename();
        $pattern = strtolower($pattern);
        $pattern = $pattern.'|'.Str::plural($pattern).'|'.$pattern.'_slug';

        return preg_replace('/\{('.$pattern.')\}/', $model->getIdentifier(), $uri);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Fetch the entries for a model.
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

    /**
     * @param $uri
     *
     * @return bool
     */
    protected function isAllowed($uri)
    {
        // Check if the route exists as it is in the allowed array.
        if (Str::contains($uri, $this->allowed)) {
            return true;
        }

        // Check if it matches any allowed as regex.
        foreach ((array) $this->allowed as $allowed) {
            if (preg_match('#'.$allowed.'#', $uri)) {
                return true;
            }
        }

        // Allow the route if the allowed array is empty.
        return empty($this->allowed);
    }
}
