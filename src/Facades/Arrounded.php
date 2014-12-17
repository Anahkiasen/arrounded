<?php
namespace Arrounded\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Arrounded
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Arrounded extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'arrounded';
	}
}
