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
