<?php
namespace Arrounded\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ScopeInterface;

/**
 * Global scope for items marked as draft.
 *
 * @author Dieter Vanden Eynde <dieterve@madewithlove.be>
 */
class DraftScope implements ScopeInterface
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @type array
     */
    protected $extensions = ['WithDrafts', 'OnlyDrafts'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function apply(Builder $builder)
    {
        $builder->where($this->getDraftColumn($builder), '=', '0');

        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function remove(Builder $builder)
    {
        $query    = $builder->getQuery();
        $bindings = $builder->getBindings();

        foreach ((array) $query->wheres as $key => $where) {
            if ($where['column'] == $this->getDraftColumn($builder)) {
                unset($query->wheres[$key]);
                unset($bindings[$key]);
                $query->wheres = array_values($query->wheres);
            }
        }

        $query->setBindings($bindings);
    }

    /**
     * Add the withDrafts extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    protected function addWithDrafts(Builder $builder)
    {
        $builder->macro('withDrafts', function (Builder $builder) {
            $this->remove($builder);

            return $builder;
        });
    }

    /**
     * Add the onlyDrafts extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    protected function addOnlyDrafts(Builder $builder)
    {
        $builder->macro('onlyDrafts', function (Builder $builder) {
            $this->remove($builder);

            $builder->getQuery()->where($this->getDraftColumn($builder), '1');

            return $builder;
        });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * @return string
     */
    protected function getDraftColumn(Builder $builder)
    {
        return $builder->getModel()->getQualifiedDraftColumn();
    }
}
