<?php
namespace Arrounded\Abstracts;

use Arrounded\Collection;
use Arrounded\Traits\ReflectionModel;
use Arrounded\Traits\Serializable;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
	use ReflectionModel;
	use Serializable;

	/**
	 * The attributes to cast on serialization
	 *
	 * @var array
	 */
	protected $casts = array(
		'integer' => ['id'],
	);

	//////////////////////////////////////////////////////////////////////
	/////////////////////////// RELATED CLASSES //////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get tge application's namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		$path = get_class($this);
		$path = explode('\\', $path);

		return head($path);
	}

	/**
	 * Create a new Eloquent Collection instance.
	 *
	 * @param  array $models
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function newCollection(array $models = array())
	{
		$custom = $this->getNamespace().'\Collection';
		if (class_exists($custom)) {
			return new $custom($models);
		}

		return new Collection($models);
	}

	/**
	 * Get the Presenter for this model
	 *
	 * @return AbstractPresenter
	 */
	public function getPresenter()
	{
		// Find custom presenter
		$presenter = $this->getNamespace().'\Presenters\\'.get_class($this).'Presenter';
		if (class_exists($presenter)) {
			return new $presenter($this);
		}

		return new AbstractPresenter($this);
	}

	/**
	 * Get the transformer instance.
	 *
	 * @return mixed
	 */
	public function getTransformer()
	{
		// Find custom transformer
		$current     = $this->getClassBasename();
		$transformer = sprintf($this->getNamespace().'\Transformers\%sTransformer', $current);

		// Else default to a default transformer
		if (!class_exists($transformer)) {
			$transformer = $this->getNamespace().'\Transformers\DefaultTransformer';
		}

		return new $transformer;
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// SCOPES ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Order entries in a specific order
	 *
	 * @param Query  $query
	 * @param string $field
	 * @param array  $values
	 *
	 * @return Query
	 */
	public function scopeOrderByField($query, $field, $values)
	{
		return $query->orderByRaw($field.' <> "'.implode('", '.$field.' <> "', $values).'"');
	}

	/**
	 * Get all models belonging to other models
	 *
	 * @param string $relation
	 * @param array  $ids
	 *
	 * @return Query
	 */
	public function scopeWhereBelongsTo($query, $relation, array $ids = array())
	{
		$ids = $ids ?: ['void'];

		return $query->whereIn($relation.'_id', $ids);
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////// SERIALIZATION ////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Cast the model to an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$model = parent::toArray();
		$model = $this->serializeEntity($model);

		return $model;
	}
}
