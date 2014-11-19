<?php
namespace Arrounded\Abstracts;

use Arrounded\Abstracts\Models\AbstractModel;
use Illuminate\Support\Facades\Input;

/**
 * An abstract class for Finders
 */
abstract class AbstractFinder
{
	/**
	 * The parent of the Query
	 *
	 * @var AbstractModel
	 */
	protected $parent;

	/**
	 * The AbstractRepository
	 *
	 * @var AbstractRepository
	 */
	protected $repository;

	/**
	 * A list of fields text search can be performed on
	 *
	 * @var array
	 */
	protected $searchableFields = array('name');

	/**
	 * The relations to eager load on the results
	 *
	 * @var array
	 */
	protected $loadedRelations = array();

	/**
	 * The base Query
	 *
	 * @var Query
	 */
	protected $query;

	/**
	 * Search for something
	 *
	 * @param AbstractRepository $repository
	 * @param AbstractModel      $parent
	 */
	public function __construct(AbstractRepository $repository, AbstractModel $parent = null)
	{
		$this->repository = $repository;

		// If we provided a parent, scope the Query to it
		if ($parent) {
			$this->setParent($parent);
		}
	}

	/**
	 * Change the core query
	 *
	 * @param AbstractModel $parent
	 * @param string        $table
	 */
	public function setParent(AbstractModel $parent, $table = null)
	{
		$table = $table ?: $parent;

		$this->parent = $parent;
		$this->query  = $table::query();
	}

	/**
	 * Get the parent
	 *
	 * @return AbstractModel
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Sets the The relations to eager load on the results.
	 *
	 * @param array $loadedRelations the loaded relations
	 *
	 * @return self
	 */
	public function setLoadedRelations(array $loadedRelations)
	{
		$this->loadedRelations = $loadedRelations;

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FILTERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Look for people matching certain terms
	 *
	 * @param string $search
	 *
	 * @return Query
	 */
	public function search($search = null)
	{
		$search = $search ?: Input::get('q');
		if (!$search) {
			return $this->query;
		}

		$search = '%'.$search.'%';
		$handle = $this->parent ? $this->parent->getTable().'.' : '';
		$handle = '';

		return $this->query->where(function ($query) use ($search, $handle) {
			foreach ($this->searchableFields as $field) {
				$query->orWhere($handle.$field, 'LIKE', $search);
			}

			return $query;
		});
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// RESULTS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the results of the current search
	 *
	 * @param integer $perPage
	 *
	 * @return Collection
	 */
	public function getResults($perPage = null)
	{
		$this->query->with($this->loadedRelations);

		$results = $perPage
			? $this->query->paginate($perPage)
			: $this->query->get();

		return $results;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Scope a query to a collection of entries
	 *
	 * @param Query   $query
	 * @param string  $field
	 * @param array   $entries
	 * @param boolean $or
	 *
	 * @return Query
	 */
	protected function scopeToEntries($query, $field, array $entries, $or = false)
	{
		if (!$entries) {
			$entries = array('void');
		}

		return $query->whereIn($field, $entries, $or ? 'or' : 'and');
	}

	/**
	 * Scope a query to only specific resources
	 *
	 * @param Query   $query
	 * @param string  $resource
	 * @param array   $entries
	 * @param boolean $or
	 *
	 * @return Query
	 */
	protected function scopeToResource($query, $resource, array $entries, $or = false)
	{
		return $this->scopeToEntries($query, $resource.'_id', $entries, $or);
	}
}
