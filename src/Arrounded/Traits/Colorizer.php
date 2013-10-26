<?php
namespace Arrounded\Traits;

use Colors\Color;

/**
 * Add colorizer capability to the class
 */
trait Colorizer
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// COLORS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Print an info
	 *
	 * @param  string $color
	 *
	 * @return string
	 */
	public function line()
	{
		print call_user_func_array('sprintf', func_get_args()).PHP_EOL;
	}

	/**
	 * Print an info
	 *
	 * @return string
	 */
	public function success()
	{
		return $this->colorize('green', func_get_args());
	}

	/**
	 * Print an info
	 *
	 * @return string
	 */
	public function info()
	{
		return $this->colorize('blue', func_get_args());
	}

	/**
	 * Print an error
	 *
	 * @return string
	 */
	public function error()
	{
		return $this->colorize('red', func_get_args());
	}

	/**
	 * Print a comment
	 *
	 * @return string
	 */
	public function comment()
	{
		return $this->colorize('yellow', func_get_args());
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Colorize a message
	 *
	 * @param  string $color
	 *
	 * @return void
	 */
	protected function colorize($color, $arguments)
	{
		$colors    = new Color;
		$arguments = (array) $arguments;

		// Format message
		$message = array_shift($arguments);
		$message = $colors($message)->$color;
		array_unshift($arguments, $message);

		return call_user_func_array([$this, 'line'], $arguments);
	}
}
