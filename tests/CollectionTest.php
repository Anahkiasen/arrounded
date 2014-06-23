<?php
namespace Arrounded;

class CollectionTest extends ArroundedTestCase
{
	/**
	 * A dummy Collection
	 *
	 * @var Collection
	 */
	protected $collection;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		$this->collection = new Collection(array(
			['name' => 'foo', 'status' => true, 'note' => 0],
			['name' => 'bar', 'status' => false, 'note' => 10],
		));
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanFilterByColumnName()
	{
		$results = $this->collection->filterBy('status')->all();

		$this->assertCount(1, $results);
		$this->assertEquals('foo', $results[0]['name']);
	}

	public function testCanComputeAverage()
	{
		$results = $this->collection->average('note');

		$this->assertEquals(5, $results);
	}

	public function testCanGetKeys()
	{
		$results = $this->collection->keys();

		$this->assertEquals([0, 1], $results);
	}

	public function testCanSortByKeys()
	{
		$results = $this->collection->sortByKeys(true);

		$this->assertEquals([1, 0], $results->keys());
	}
}
