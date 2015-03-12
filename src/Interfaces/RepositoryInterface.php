<?php
namespace Arrounded\Interfaces;

use Arrounded\Abstracts\Models\AbstractModel;
use Arrounded\Collection;
use Illuminate\Pagination\Paginator;

interface RepositoryInterface
{
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// CORE DATA ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Eager load relations on the base Query.
     *
     * @param array $relations
     *
     * @return self
     */
    public function eagerLoad($relations);

    /**
     * Change the core items.
     *
     * @param AbstractModel $items
     *
     * @return \Arrounded\Abstracts\AbstractRepository
     */
    public function setItems($items);

    /**
     * Get the core items query.
     *
     * @return AbstractModel
     */
    public function items();

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// SINGLE ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Find a particular item.
     *
     * @param int $item
     *
     * @return AbstractModel
     */
    public function find($item);

    /**
     * Find or instantiate an instance of an item from a set of attributes.
     *
     * @param array $attributes
     *
     * @return AbstractModel
     */
    public function findOrNew($attributes = []);

    /**
     * Create an entry from an array of attributes.
     *
     * @param array $attributes
     *
     * @return AbstractModel
     */
    public function create(array $attributes = []);

    /**
     * Update an item.
     *
     * @param AbstractModel|int $item
     * @param array             $attributes
     *
     * @return AbstractModel
     */
    public function update($item, array $attributes = []);

    /**
     * Delete an item.
     *
     * @param AbstractModel|int $item
     * @param bool              $force
     *
     * @return bool
     */
    public function delete($item, $force);

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// MULTIPLE ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Return all items.
     *
     * @param int|null $perPage
     *
     * @return Collection
     */
    public function all($perPage = null);

    /**
     * Get all items, paginated.
     *
     * @param int $perPage
     *
     * @return Paginator
     */
    public function getPaginated($perPage = 25);
}
