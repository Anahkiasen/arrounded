<?php
namespace Arrounded\Traits;

use Auth;
use HTML;
use Illuminate\Support\Str;
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
	////////////////////////////// REFLECTION //////////////////////////
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
	 * Get the model's base class
	 *
	 * @return string
	 */
	public function getClassBasename()
	{
		return class_basename($this->getClass());
	}

	/**
	 * Get tge application's namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		$path = get_class($this);
		$path = explode('\\', $path);

		return array_get($path, 0);
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// ROUTING ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

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
	public function getLink($action, $title = null, array $attributes = array())
	{
		$title = $title ?: $this->name;

		return HTML::linkAction($this->getAction($action), $title, $this->slug, $attributes);
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////// RELATED CLASSES //////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the presenter instance
	 *
	 * @return AbstractPresenter
	 */
	public function getPresenter()
	{
		return $this->getRelatedClass('Presenter', $this->getNamespace().'\Presenters\DefaultPresenter');
	}

	/**
	 * Get the transformer instance.
	 *
	 * @return AbstractTransformer
	 */
	public function getTransformer()
	{
		return $this->getRelatedClass('Transformer', $this->getNamespace().'\Transformers\DefaultTransformer');
	}

	/**
	 * Get a related class
	 *
	 * @param string $type
	 * @param string $default
	 *
	 * @return string
	 */
	public function getRelatedClass($type, $default)
	{
		// Find custom class
		$transformer = sprintf('%s\%ss\%s%s', $this->getNamespace(), $type, $this->getClassBasename(), $type);

		// Else default to a default class
		if (!class_exists($transformer)) {
			$transformer = $default;
		}

		return new $transformer($this);
	}

	/**
	 * Check if the model uses a trait
	 *
	 * @param  string $trait
	 *
	 * @return boolean
	 */
	public function hasTrait($trait)
	{
		// Try both given name and fully qualified name
		$qualified = 'Arrounded\Traits\\'.$trait;
		$traits    = $this->classUsesDeep($this);

		return in_array($trait, $traits) || in_array($qualified, $traits);
	}

	/**
	 * Get all traits used by a class and its parents
	 *
	 * @param string|object $class
	 * @param boolean       $autoload
	 *
	 * @return array
	 */
	protected function classUsesDeep($class, $autoload = true)
	{
		$traits = [];
		do {
			$traits = array_merge(class_uses($class, $autoload), $traits);
		} while ($class = get_parent_class($class));
		foreach ($traits as $trait => $same) {
			$traits = array_merge(class_uses($trait, $autoload), $traits);
		}

		return array_unique($traits);
	}
}
