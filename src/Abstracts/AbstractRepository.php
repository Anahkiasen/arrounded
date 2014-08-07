<?php
namespace Arrounded\Abstracts;

use Arrounded\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository implements RepositoryInterface
{
	/**
	 * The items to fetch from
	 *
	 * @var AbstractModel
	 */
	protected $items;

	/**
	 * Default number of results per page
	 *
	 * @var integer
	 */
	protected $perPage = 25;

	/**
	 * Get the core model instance
	 *
	 * @return Model
	 */
	public function getModelInstance()
	{
		$model = $this->items;
		if (method_exists($model, 'getModel')) {
			$model = $model->getModel();
		}

		return $model;
	}

	/**
	 * Get the name of the model
	 *
	 * @return string
	 */
	public function getModel()
	{
		return get_class($this->getModelInstance());
	}

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
	 * Set the number of results to display per page
	 *
	 * @param integer $perPage
	 *
	 * @return self
	 */
	public function setPerPage($perPage = null)
	{
		if ($perPage) {
			$this->perPage = $perPage;
		}

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

		return $this;
	}

	/**
	 * Get the core items query
	 *
	 * @return Query
	 */
	public function items()
	{
		if ($this->items instanceof Builder) {
			$this->items = $this->items->getModel();
		}

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
		if (!preg_match('/^[0-9]+$/', $item) and $this->getModelInstance()->hasTrait('Sluggable')) {
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
			? $this->find($item)->fill($attributes)
			: $this->items->newInstance($attributes);
	}

	/**
	 * Create an entry from an array of attributes
	 *
	 * @param  array $attributes
	 *
	 * @return AbstractModel
	 */
	public function create(array $attributes = array())
	{
		// Create model and fetch it back
		$item = $this->items()->create($attributes);
		$item = $this->find($item->id);
		$item = $this->onUpdate($item, $attributes);

		return $item;
	}

	/**
	 * Update an item
	 *
	 * @param AbstractModel|integer $item
	 * @param array                 $attributes
	 *
	 * @return AbstractModel
	 */
	public function update($item, array $attributes = array())
	{
		$item = $this->find($item);
		$item->fill($attributes)->save();
		$item = $this->onUpdate($item, $attributes);

		return $item;
	}

	/**
	 * Finds an item, including trashed ones
	 *
	 * @param  $item
	 * @return AbstractModel
	 */
	public function findInTrash($item)
	{
		return $this->items->withTrashed()->findOrFail($item);
	}

	/**
	 * Delete an item
	 *
	 * @param AbstractModel|integer $item
	 * @param boolean               $force Force delete or not
	 *
	 * @return boolean
	 */
	public function delete($item, $force = false)
	{
		$method = $force ? 'forceDelete' : 'delete';
		$item   = $this->find($item);

		return $item->$method();
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HOOKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Hook for when a model is created/updated
	 *
	 * @param AbstractModel $model
	 * @param array         $attributes
	 *
	 * @return AbstractModel
	 */
	protected function onUpdate($model, $attributes)
	{
		return $model;
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
	public function getPaginated($perPage = null)
	{
		$perPage = $perPage ?: $this->perPage;

		return $this->items->paginate($perPage);
	}
}
