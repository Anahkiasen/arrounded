<?php
namespace Arrounded;

use Paginator;

/**
 * Collection class with additional helpers
 */
class Collection extends \Illuminate\Database\Eloquent\Collection
{
	/**
	 * Serialize an array of data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function serialize(array $data)
	{
		return static::make($data)->toArray();
	}

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
	 * Paginate a Collection
	 *
	 * @param integer $perPage
	 *
	 * @return Paginator
	 */
	public function paginate($perPage)
	{
		$count = $this->count();
		$page  = Paginator::getCurrentPage($count);
		$items = $this->slice(($page - 1) * $perPage, $perPage)->all();

    return Paginator::make($items, $count, $perPage);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// AGGREGATES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Return the number of entries in each subarray
	 *
	 * @return self
	 */
	public function counts()
	{
		// do not modify the source collection
		$self = clone $this;

		foreach ($self->items as &$item) {
			$item = sizeof($item);
		}

		return $self;
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

	/**
	 * Compute the sum of the Collection
	 *
	 * @param Callback|null $callback
	 *
	 * @return mixed
	 */
	public function sum($callback = null)
	{
		if ($callback) {
			return parent::sum($callback);
		}

		return array_sum($this->toArray());
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
