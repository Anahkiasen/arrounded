<?php
namespace Arrounded;

use Illuminate\Foundation\Console\RoutesCommand;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ApiDisplayer extends RoutesCommand
{
    /**
     * The namespace to filter with.
     *
     * @type string
     */
    protected $namespace = 'Api\\';

    /**
     * Filter the route by URI and / or name.
     *
     * @param array $route
     *
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        return Str::contains($route['action'], $this->namespace) ? $route : null;
    }

    /**
     * Get the route information for a given route.
     *
     * @param Route $route
     *
     * @return array|null
     */
    protected function getRouteInformation(Route $route)
    {
        $route = parent::getRouteInformation($route);
        if (!$route) {
            return;
        }

        // Separate method and URI
        list($methods, $uri) = explode(' ', $route['uri']);
        $route['methods']    = $methods;
        $route['uri']        = $uri;

        return $route;
    }

    /**
     * Get the routes as a collection.
     *
     * @return Collection
     */
    public function getRoutesCollection()
    {
        return Collection::make($this->getRoutes())->sortBy('uri');
    }
}
