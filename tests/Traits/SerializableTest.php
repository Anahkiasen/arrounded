<?php
namespace Arrounded\Traits;

use Arrounded\ArroundedTestCase;
use Arrounded\Dummies\DummyModel;

class SerializableTest extends ArroundedTestCase
{
	/**
	 * A dummy instance
	 *
	 * @var DummyModel
	 */
	protected $model;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		$this->model = new DummyModel;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanCastAttributes()
	{
		$this->model->id     = '1';
		$this->model->status = 'true';

		$model = $this->model->serializeEntity();
		$this->assertEquals(1, $model['id']);
		$this->assertEquals(true, $model['status']);
	}
}
