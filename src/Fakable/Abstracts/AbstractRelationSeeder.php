<?php
namespace Fakable\Abstracts;

use Fakable\Fakable;

/**
 * Base class for seeding a relation
 */
abstract class AbstractRelationSeeder
{
	/**
	 * The Fakable instance
	 *
	 * @var Fakable
	 */
	protected $fakable;

	/**
	 * The relation to seed
	 *
	 * @var Relation
	 */
	protected $relation;

	/**
	 * Build a new RelationSeeder
	 *
	 * @param Fakable $fakable
	 */
	public function __construct(Fakable $fakable, $relation)
	{
		$this->fakable  = $fakable;
		$this->relation = $relation;
	}

	/**
	 * Call a method on the relation
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->relation, $method], $parameters);
	}

	/**
	 * Affect a model's attribute
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function affectAttributes(array $attributes)
	{
		return $attributes;
	}

	/**
	 * Generate an entry
	 *
	 * @return array
	 */
	public function generateEntry()
	{
		return array();
	}

	/**
	 * Generate multiple entries
	 *
	 * @param integer  $min
	 * @param integer  $max
	 *
	 * @return array
	 */
	public function generateEntries($min = 5, $max = null)
	{
		$entries = [];
		$max = $max ?: $min + 5;
		for ($i = 0; $i < $max; $i++) {
			$entries[] = $this->generateEntry();
		}

		return $entries;
	}
}
