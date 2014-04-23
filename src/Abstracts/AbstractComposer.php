<?php
namespace Arrounded\Abstracts;

use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

/**
 * An abstract composer class with helpers
 */
abstract class AbstractComposer
{
	use UsesContainer;

	/**
	 * Make a menu from a list of links
	 *
	 * @param  array $menu
	 *
	 * @return array
	 */
	protected function makeMenu($menu)
	{
		foreach ($menu as $key => $item) {
			// Rebuild from associative array
			if (is_string($item)) {
				$item = array($key, $item);
			}

			list ($endpoint, $label) = $item;
			$attributes = array_get($item, 4, array());

			// Compute actual URL
			$parameters = array_get($item, 2, array());
			$link = Str::contains($endpoint, '@')
				? $this->app['url']->action($endpoint, $parameters)
				: $this->app['url']->to($endpoint, $parameters);

			// Compute active state
			if ($link !== '#') {
				$active = array_get($item, 3) ?: str_replace($this->app['request']->root().'/', null, $link);
				$active = preg_match("#$active#", $this->app['request']->path());
			} else {
				$active = false;
			}

			$links[] = array_merge(array(
				'endpoint' => $link,
				'label'    => $this->translate($label),
				'active'   => $active ? 'active' : false,
			), $attributes);
		}

		return $links;
	}

	/**
	 * Act on a string to translate it
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function translate($string)
	{
		return $this->app['translator']->get($string);
	}
}
