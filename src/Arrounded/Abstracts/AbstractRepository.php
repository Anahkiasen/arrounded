<?php
namespace Arrounded\Abstracts;

use Arrounded\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository implements RepositoryInterface
{
	/**
	 * The items to fetch from
	 *
	 * @var AbstractModel
	 */
	protected $items;

	////////////////////////////////////////////////////////////////////
	////////////////////////////// CORE DATA ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Eager load relations on the base Query
	 *
	 * @param array $relations
	 *
	 * @return self
	 */
	public function eagerLoad($relations)
	{
		$this->items = $this->items->with($relations);

		return $this;
	}

	/**
	 * Change the core items
	 *
	 * @param AbstractModel $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

	/**
	 * Get the core items query
	 *
	 * @return Query
	 */
	public function items()
	{
		return clone $this->items;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// SINGLE ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Find a particular item
	 *
	 * @param  integer $item
	 *
	 * @return AbstractModel
	 */
	public function find($item)
	{
		// If we have an instance already, return it
		if ($item instanceof Model) {
			return $item;
		}

		// Find by slug
		if ((int) $item === 0) {
			return $this->items()->whereSlug($item)->firstOrFail();
		}

		return $this->items()->findOrFail($item);
	}

	/**
	 * Find or instantiate an instance of an item from a set of attributes
	 *
	 * @param array $attributes
	 *
	 * @return AbstractModel
	 */
	public function findOrNew($attributes = array())
	{
		$item = array_get($attributes, 'id');

		return $item
			?	$this->items->find($item)->fill($attributes)
			: $this->items->newInstance($attributes);
	}

	/**
	 * Create an entry from an array of attributes
	 *
	 * @param  array  $attributes
	 *
	 * @return AbstractModel
	 */
	public function create(array $attributes = array())
	{
		// Create model and fetch it back
		$model = $this->items->create($attributes);
		$model = $this->find($model->id);

		return $model;
	}

	/**
	 * Update an item
	 *
	 * @param AbstractModel|integer $item
	 * @param array                 $attributes
	 *
	 * @return boolean
	 */
	public function update($item, array $attributes = array())
	{
		$item  = $this->find($item);
		$saved = $item->fill($attributes)->save();

		return $saved;
	}

	/**
	 * Delete an item
	 *
	 * @param AbstractModel|integer $item
	 *
	 * @return boolean
	 */
	public function delete($item)
	{
		return $this->find($item)->delete();
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// MULTIPLE ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Return all items
	 *
	 * @param integer $perPage
	 *
	 * @return Collection
	 */
	public function all($perPage = null)
	{
		return $perPage
			? $this->getPaginated($perPage)
			: $this->items->get();
	}

	/**
	 * Get all items, paginated
	 *
	 * @param integer $perPage
	 *
	 * @return Paginator
	 */
	public function getPaginated($perPage = 25)
	{
		return $this->items->paginate($perPage);
	}
}
