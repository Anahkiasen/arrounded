<?php
namespace Arrounded\Macros;

class HtmlBuilder extends \Illuminate\Html\HtmlBuilder
{
	/**
	 * Generates metadata
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function metadata(array $attributes = array())
	{
		return app('arrounded.meta')->render($attributes);
	}
}
