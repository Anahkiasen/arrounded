<?php
use Arrounded\Abstracts\AbstractRepository;

/**
 * A dummy repository implementation
 */
class DummyRepository extends AbstractRepository
{
	/**
	 * Build a new DummyRepository
	 *
	 * @param mixed $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}
}
