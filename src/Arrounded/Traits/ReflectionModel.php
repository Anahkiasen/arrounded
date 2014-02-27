<?php
namespace Arrounded\Traits;

/**
 * A model with methods that connect to routes and controllers
 */
trait ReflectionModel
{
	/**
	 * Get the model's class
	 *
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}

	/**
	 * Get the controller matching the model
	 *
	 * @return string
	 */
	public function getController()
	{
		return ucfirst($this->getTable()).'Controller';
	}

	/**
	 * Get an action from the model's controller
	 *
	 * @param string  $action
	 * @param boolean $api
	 *
	 * @return string
	 */
	public function getAction($action, $api = false)
	{
		$prefix  = $api ? 'Api\\' : '';
		$prefix .= $this->getController().'@';

		return $prefix.$action;
	}

	/**
	 * Get the link to an action
	 *
	 * @param string  $action
	 * @param boolean $api
	 *
	 * @return string
	 */
	public function getLink($action, $api = false)
	{
		return URL::action($this->getAction($action, $api), $this->slug);
	}

	/**
	 * Check if the model uses a trait
	 *
	 * @param  string  $trait
	 *
	 * @return boolean
	 */
	public function hasTrait($trait)
	{
		// Try both given name and fully qualified name
		$qualified = 'Arrounded\Traits\\' .$trait;
		$traits    = class_uses($this);

		return in_array($trait, $traits) || in_array($qualified, $traits);
	}
}
