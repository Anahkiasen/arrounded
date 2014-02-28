<?php
namespace Arrounded\Traits;

use Illuminate\Container\Container;

/**
 * A class using the container underneath
 */
trait UsesContainer
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Default construct for a container-based class
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Get an entry from the Container
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function __get($key)
	{
		return $this->app[$key];
	}
}
