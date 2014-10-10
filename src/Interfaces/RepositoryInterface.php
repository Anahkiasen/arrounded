<?php
namespace Arrounded\Interfaces;

use Arrounded\Abstracts\AbstractModel;

interface RepositoryInterface
{
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
	public function eagerLoad($relations);

	/**
	 * Change the core items
	 *
	 * @param AbstractModel $items
	 *
	 * @return \Arrounded\Abstracts\AbstractRepository
	 */
	public function setItems($items);

	/**
	 * Get the core items query
	 *
	 * @return Query
	 */
	public function items();

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
	public function find($item);

	/**
	 * Find or instantiate an instance of an item from a set of attributes
	 *
	 * @param array $attributes
	 *
	 * @return AbstractModel
	 */
	public function findOrNew($attributes = array());

	/**
	 * Create an entry from an array of attributes
	 *
	 * @param  array $attributes
	 *
	 * @return AbstractModel
	 */
	public function create(array $attributes = array());

	/**
	 * Update an item
	 *
	 * @param AbstractModel|integer $item
	 * @param array                 $attributes
	 *
	 * @return AbstractModel
	 */
	public function update($item, array $attributes = array());

	/**
	 * Delete an item
	 *
	 * @param AbstractModel|integer $item
	 *
	 * @return boolean
	 */
	public function delete($item);

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
	public function all($perPage = null);

	/**
	 * Get all items, paginated
	 *
	 * @param integer $perPage
	 *
	 * @return Paginator
	 */
	public function getPaginated($perPage = 25);
}
