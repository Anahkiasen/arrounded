<?php
namespace Arrounded\Traits\Scopes;

trait DraftTrait
{
	/**
	 * @return void
	 */
	public static function bootDraftListingsTrait()
	{
		static::addGlobalScope(new DraftListingsScope());
	}

	/**
	 * Get a new query builder that includes drafts.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public static function withDrafts()
	{
		return with(new static())->newQueryWithoutScope(new DraftListingsScope());
	}

	/**
	 * Get a new query builder that includes drafts.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public static function onlyDrafts()
	{
		$instance = new static();

		return with($instance)->newQueryWithoutScope(new DraftListingsScope())->where($instance->getQualifiedDraftColumn(), '1');
	}

	/**
	 * Get the name of the draft column
	 *
	 * @return string
	 */
	public function getQualifiedDraftColumn()
	{
		$table  = $this->getTable();
		$column = $table.'.is_draft';

		return $column;
	}
}
