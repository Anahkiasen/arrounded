<?php
namespace Arrounded\Abstracts;

use Arrounded\Abstracts\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Input;

/**
 * An abstract class for Finders.
 */
abstract class AbstractFinder
{
    /**
     * The parent of the Query.
     *
     * @type AbstractModel
     */
    protected $parent;

    /**
     * The AbstractRepository.
     *
     * @type AbstractRepository
     */
    protected $repository;

    /**
     * A list of fields text search can be performed on.
     *
     * @type array
     */
    protected $searchableFields = ['name'];

    /**
     * The relations to eager load on the results.
     *
     * @type array
     */
    protected $loadedRelations = [];

    /**
     * The base Query.
     *
     * @type Query
     */
    protected $query;

    /**
     * Search for something.
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

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// PARENT ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Include deleted records in the search.
     */
    public function includeDeleted()
    {
        if ($this->parent->softDeletes()) {
            $this->query = $this->parent->query()->withTrashed();
        }
    }

    /**
     * Change the core query.
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
     * Get the parent.
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
     * Look for entries matching a term.
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

        return $this->query->where(function (Builder $query) use ($search) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', $this->formatValue($search));
            }

            return $query;
        });
    }

    /**
     * Look for entries matching multiple attributes.
     *
     * @param array $search
     */
    public function multisearch($search = [])
    {
        $search = $search ?: Input::all();
        if (!$search) {
            return $this->query;
        }

        // Filter input
        $attributes = [];
        foreach ($search as $name => $value) {
            if ($value && $this->isSearchable($name)) {
                $attributes[$name] = $value;
            }
        }

        return $this->query->where(function (Builder $query) use ($attributes) {

            foreach ($attributes as $name => $value) {
                if (in_array($name, $this->searchableFields)) {
                    $this->scopeSearchOnField($query, $name, $value);
                } else {
                    $query->orWhereHas($name, function (Builder $query) use ($value) {
                        return $this->scopeSearchOnField($query, 'name', $value);
                    });
                }
            }

            return $query;
        });
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// RESULTS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the query.
     *
     * @param bool $string
     *
     * @return Query|string
     */
    public function getQuery($string = false)
    {
        $query = $this->query;

        if ($string) {
            // Unnest query objects
            while (method_exists($query, 'getQuery')) {
                $query = $query->getQuery();
            }

            // Replace bindings
            $sql = str_replace('?', '%s', $query->toSql());
            $sql = vsprintf($sql, $query->getBindings());

            return $sql;
        }

        return $query;
    }

    /**
     * Get the results of the current search.
     *
     * @param int $perPage
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
     * Scope a query to a collection of entries.
     *
     * @param Query  $query
     * @param string $field
     * @param array  $entries
     * @param bool   $or
     *
     * @return Query
     */
    protected function scopeToEntries($query, $field, array $entries, $or = false)
    {
        if (!$entries) {
            $entries = ['void'];
        }

        return $query->whereIn($field, $entries, $or ? 'or' : 'and');
    }

    /**
     * Scope a query to only specific resources.
     *
     * @param Query  $query
     * @param string $resource
     * @param array  $entries
     * @param bool   $or
     *
     * @return Query
     */
    protected function scopeToResource($query, $resource, array $entries, $or = false)
    {
        return $this->scopeToEntries($query, $resource.'_id', $entries, $or);
    }

    /**
     * Apply search conditions to a queyr.
     *
     * @param Builder $query
     * @param string  $field
     * @param string  $value
     *
     * @return Builder
     */
    protected function scopeSearchOnField(Builder &$query, $field, $value, $or = true)
    {
        return $query->where($field, 'LIKE', $this->formatValue($value))->orWhere($field, $value);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Format a value for a search.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        return '%'.$value.'%';
    }

    /**
     * Check if a field is searchable.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isSearchable($name)
    {
        $model = $this->repository->getModelInstance();

        return in_array($name, $this->searchableFields) || in_array($name, $model->getAvailableRelations());
    }
}
