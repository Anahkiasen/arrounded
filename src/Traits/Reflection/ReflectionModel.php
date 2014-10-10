<?php
namespace Arrounded\Traits\Reflection;

use Arrounded\Traits\AbstractPresenter;
use Arrounded\Traits\AbstractTransformer;
use Auth;
use HTML;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use URL;

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
		return $this->getRelatedClass('Transformer', array(
			$this->getNamespace().'\Transformers\DefaultTransformer',
			'Arrounded\Services\Transformers\DefaultTransformer',
		));
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
