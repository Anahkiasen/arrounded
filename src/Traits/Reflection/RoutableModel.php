<?php
namespace Arrounded\Traits\Reflection;

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait RoutableModel
{
	/**
	 * Get the controller matching the model
	 *
	 * @return string
	 */
	public function getController()
	{
		$name = $this->getClass();
		$name = class_basename($name);
		$name = Str::plural($name);

		return $name.'Controller';
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
		$prefix = $api ? 'Api\\' : '';
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
		return URL::action($this->getAction($action, $api), $this->getIdentifier());
	}

	/**
	 * Get the link to an action
	 *
	 * @param string      $action
	 * @param string|null $title
	 * @param array       $attributes
	 *
	 * @return string
	 */
	public function getLink($action, $title = null, array $attributes = array())
	{
		$title = $title ?: $this->name;

		return HTML::linkAction($this->getAction($action), $title, $this->getIdentifier(), $attributes);
	}
}
