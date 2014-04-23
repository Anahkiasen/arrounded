<?php
namespace Arrounded\Abstracts;

/**
 * A class that decorates around other classes
 */
abstract class AbstractDecorator
{
	/**
	 * An array of classes to decorate
	 *
	 * @var array
	 */
	protected $decorates = array();

	/**
	 * Build a new decorator
	 */
	public function __construct()
	{
		$this->decorates = func_get_args();
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// DECORATION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the decorated services
	 *
	 * @return void
	 */
	protected function getServices()
	{
		$services = array();
		foreach ($this->decorates as $service) {
			if (is_string($service)) {
				$service = $this->$service;
			}

			$services[] = $service;
		}

		return $services;
	}

	/**
	 * Delegate a call to the services
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return Response
	 */
	public function __call($method, $parameters)
	{
		foreach ($this->getServices() as $service) {
			if (method_exists($service, $method)) {
				return call_user_func_array([$service, $method], $parameters);
			}
		}
	}

	/**
	 * Get an attribute from the services
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		foreach ($this->getServices() as $service) {
			if (isset($service->$key)) {
				return $service->$key;
			}
		}
	}

	/**
	 * Check if a key isset in the services
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset($key)
	{
		$isset = false;
		foreach ($this->getServices() as $service) {
			if (isset($service->$key)) {
				$isset = true;
			}
		}

		return $isset;
	}
}
