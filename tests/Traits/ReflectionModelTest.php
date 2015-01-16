<?php
namespace Arrounded\Traits;

use Arrounded\Dummies\DummyModel;
use Arrounded\TestCases\ArroundedTestCase;

class ReflectionModelTest extends ArroundedTestCase
{
    /**
     * A dummy instance
     *
     * @type DummyModel
     */
    protected $model;

    /**
     * Set up the tests
     */
    public function setUp()
    {
        $this->model = new DummyModel();
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetClass()
    {
        $this->assertEquals('Arrounded\Dummies\DummyModel', $this->model->getClass());
    }

    public function testCanGetController()
    {
        $this->assertEquals('DummyModelsController', $this->model->getController());
    }

    public function testCanGetAction()
    {
        $this->assertEquals('DummyModelsController@foobar', $this->model->getAction('foobar'));
    }

    public function testCanGetApiAction()
    {
        $this->assertEquals('Api\DummyModelsController@foobar', $this->model->getAction('foobar', true));
    }

    public function testCanCheckIfHasTrait()
    {
        $this->assertTrue($this->model->hasTrait('ReflectionModel'));
        $this->assertFalse($this->model->hasTrait('Nopeable'));
    }
}
