<?php
namespace Arrounded;

use Illuminate\Support\Contracts\JsonableInterface;

class JavascriptBridge
{
	/**
	 * And array of data to pass to Javascript
	 *
	 * @var array
	 */
	protected static $data = array();

	/**
	 * Add data to pass
	 *
	 * @param array $data
	 */
	public static function add(array $data)
	{
		// Filter and merge data
		$data = array_filter($data);
		$data = array_merge(static::$data, $data);

		static::$data = $data;
	}

	/**
	 * Render to JS
	 *
	 * @return string
	 */
	public static function render()
	{
		$rendered = '';
		foreach (static::$data as $key => $value) {
			$encoded   = $value instanceof JsonableInterface ? $value->toJson() : json_encode($value);
			$rendered .= sprintf("\tvar %s = %s;".PHP_EOL, $key, $encoded);
		}

		return $rendered;
	}
}
