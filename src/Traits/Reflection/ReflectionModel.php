<?php
namespace Arrounded\Traits\Reflection;

use Auth;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * A model with methods that connect to routes and controllers
 */
trait ReflectionModel
{
	use RoutableModel;

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
	 * Get the object's identifier
	 *
	 * @return string|integer
	 */
	public function getIdentifier()
	{
		return $this->slug ?: $this->id;
	}

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
	 * Get the application's namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return app('arrounded')->getNamespace();
	}

	/**
	 * Get the model's available relations
	 *
	 * @return array
	 */
	public function getAvailableRelations()
	{
		$reflection = new ReflectionClass($this);

		// Gather uninherited public methods
		$relations = [];
		$methods   = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			if (
				$method->getDeclaringClass()->getName() === $reflection->getName() &&
				!Str::startsWith($method->getName(), ['get', 'scope'])
			) {
				$relations[] = $method->getName();
			}
		}

		return $relations;
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////// RELATED CLASSES //////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the presenter instance
	 *
	 * @return string
	 */
	public function getPresenter()
	{
		$service = $this->getRelatedClass('Presenter', $this->getNamespace().'\Presenters\DefaultPresenter');

		return new $service($this);
	}

	/**
	 * Get the transformer instance.
	 *
	 * @return string
	 */
	public function getTransformer()
	{
		$service = $this->getRelatedClass('Transformer', array(
			$this->getNamespace().'\Transformers\DefaultTransformer',
			'Arrounded\Services\Transformers\DefaultTransformer',
		));

		return new $service;
	}

	/**
	 * Get a related class
	 *
	 * @param string          $type
	 * @param string|string[] $default
	 *
	 * @return string
	 */
	public function getRelatedClass($type, $default)
	{
		return app('arrounded')->getModelService($this->getClassBasename(), $type, $default);
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// TRAITS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Whether the model soft deletes or not
	 *
	 * @return boolean
	 */
	public function softDeletes()
	{
		return $this->hasTrait('Illuminate\Database\Eloquent\SoftDeletingTrait');
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
		$places = array(
			'Arrounded\Traits\%s',
			'Arrounded\Traits\Reflection\%s',
			'%s',
		);

		$traits = class_uses_recursive($this->getClass());
		foreach ($places as $place) {
			$place = sprintf($place, $trait);
			if (in_array($place, $traits)) {
				return true;
			}
		}

		return false;
	}
}
