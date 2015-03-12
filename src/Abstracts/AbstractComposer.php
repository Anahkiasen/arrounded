<?php
namespace Arrounded\Abstracts;

use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

/**
 * An abstract composer class with helpers.
 */
abstract class AbstractComposer
{
    use UsesContainer;

    /**
     * Make a menu from a list of links.
     *
     * @param array $menu
     *
     * @return array
     */
    protected function makeMenu($menu)
    {
        $links = [];
        foreach ($menu as $key => $item) {
            // Rebuild from associative array
            if (is_string($item)) {
                $item = [$key, $item];
            }

            list($endpoint, $label) = $item;
            $attributes             = array_get($item, 4, []);

            // Compute actual URL
            $parameters = array_get($item, 2, []);
            $link       = Str::contains($endpoint, '@')
                ? $this->app['url']->action($endpoint, $parameters)
                : $this->app['url']->to($endpoint, $parameters);

            // Compute active state
            if ($link !== '#') {
                $active = array_get($item, 3) ?: str_replace($this->app['request']->root().'/', null, $link);
                $active = $this->isOnPage($active);
            } else {
                $active = false;
            }

            $links[] = array_merge([
                'endpoint' => $link,
                'label'    => $this->translate($label),
                'active'   => $active ? 'active' : false,
            ], $attributes);
        }

        return $links;
    }

    /**
     * Check if a string matches the given url.
     *
     * @param string $page
     *
     * @return int
     */
    protected function isOnPage($page, $loose = true)
    {
        $page = $loose ? $page : '^'.$page.'$';
        $page = str_replace('#', '\#', $page);

        return preg_match('#'.$page.'#', $this->app['request']->path());
    }

    /**
     * Act on a string to translate it.
     *
     * @param string $string
     *
     * @return string
     */
    protected function translate($string)
    {
        $translated = $this->app['translator']->get($string);

        return is_string($translated) ? $translated : $string;
    }
}
