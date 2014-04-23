<?php
namespace Arrouned;

/**
 * Collection class with additional helpers
 */
class Collection extends \Illuminate\Database\Eloquent\Collection
{
	/**
	 * Filter items by a column
	 *
	 * @param string $column
	 *
	 * @return self
	 */
	public function filterBy($column = null)
	{
		return $this->filter(function ($item) use ($column) {
			return $column ? data_get($item, $column) : $item;
		});
	}

	/**
	 * Gather the first items of all subarrays
	 *
	 * @return self
	 */
	public function gatherFirsts()
	{
		return $this->transform(function ($items) {
			return head($items);
		});
	}

	/**
	 * Shuffle the Collection
	 *
	 * @return self
	 */
	public function shuffle()
	{
		shuffle($this->items);

		return $this;
	}

	/**
	 * Get the average of a Collection
	 *
	 * @return integer
	 */
	public function average($key = null)
	{
		$results = $key ? $this->lists($key) : $this->items;
		$results = array_sum($results) / sizeof($results);

		return $results;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////////// KEYS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the keys of a Collection
	 *
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->items);
	}

	/**
	 * Sort the core items by key
	 *
	 * @param boolean $reverse
	 *
	 * @return self
	 */
	public function sortByKeys($reverse = false)
	{
		$sorting = $reverse ? 'krsort' : 'ksort';
		$sorting($this->items);

		return $this;
	}
}
