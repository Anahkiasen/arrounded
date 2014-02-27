<?php
namespace Arrounded\Traits;

use Auth;
use HTML;
use URL;

/**
 * A model with methods that connect to routes and controllers
 */
trait ReflectionModel
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STATE /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Whether the model belongs to the currently authentified user
	 *
	 * @return boolean
	 */
	public function belongsToCurrent()
	{
		return Auth::check() && Auth::user()->id == $this->user_id;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// ROUTES ////////////////////////////
	////////////////////////////////////////////////////////////////////

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
	 * Get the path to an action
	 *
	 * @param string  $action
	 * @param boolean $api
	 *
	 * @return string
	 */
	public function getPath($action, $api = false)
	{
		return URL::action($this->getAction($action, $api), $this->slug);
	}

	/**
	 * Get the link to an action
	 *
	 * @param string $action
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function getLink($action, array $attributes = array())
	{
		return HTML::linkAction($this->getAction($action), $this->name, $this->slug, $attributes);
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
