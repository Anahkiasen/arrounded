<?php
namespace Arrounded\Abstracts;

use Arrounded\Dummies\DummyModel;
use Arrounded\Dummies\DummyRepository;
use Arrounded\TestCases\ArroundedTestCase;
use Mockery;

class AbstractRepositoryTest extends ArroundedTestCase
{
	////////////////////////////////////////////////////////////////////
	////////////////////////////// TESTS ///////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanSetAndGetCoreItems()
	{
		$items = new DummyModel(array('name' => 'foo'));

		$repository = new DummyRepository($items);
		$repository->setItems(new DummyModel(array('name' => 'bar')));

		$this->assertEquals('bar', $repository->items()->name);
	}

	public function testCanFindItem()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('findOrFail')->once()->with(1, null)->andReturn('Model1');
		});

		$repository = new DummyRepository($eloquent);

		$this->assertEquals('Model1', $repository->find(1));
	}

	public function testCanReturnAlreadyFoundInstances()
	{
		$repository = new DummyRepository(new DummyModel());
		$model      = new DummyModel();

		$this->assertEquals($model, $repository->find($model));
	}

	public function testCanFindItemViaAttributes()
	{
		$eloquent   = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('findOrFail')->once()->with(1, null)->andReturn(new DummyModel());
		});
		$repository = new DummyRepository($eloquent);
		$model      = $repository->findOrNew(array('id' => 1, 'name' => 'foo'));

		$this->assertEquals('foo', $model->name);
	}

	public function testCanInstnatiateViaAttributes()
	{
		$eloquent   = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('newInstance')->once()->with(array('name' => 'foo'))->andReturn(new DummyModel(array('name' => 'foo')));
		});
		$repository = new DummyRepository($eloquent);
		$model      = $repository->findOrNew(array('name' => 'foo'));

		$this->assertEquals('foo', $model->name);
	}

	public function testCanCreateItem()
	{
		$eloquent   = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('create')->once()->with(array('name' => 'foo'))->andReturn(new DummyModel(array('id' => 1)));
			$mock->shouldReceive('findOrFail')->once()->with(1, null)->andReturn(new DummyModel(array('name' => 'foo')));
		});
		$repository = new DummyRepository($eloquent);
		$model      = $repository->create(array('name' => 'foo'));

		$this->assertEquals('foo', $model->name);
	}

	public function testCanUpdateItem()
	{
		$model = Mockery::mock('Model', function ($mock) {
			$mock->shouldReceive('save')->once()->andReturn(true);
			$mock->shouldReceive('fill')->once()->with(array('name' => 'foo'))->andReturn($mock);
		});

		$eloquent = Mockery::mock('Eloquent', function ($mock) use ($model) {
			$mock->shouldReceive('findOrFail')->once()->with(1, null)->andReturn($model);
		});

		$repository = new DummyRepository($eloquent);
		$item       = $repository->update(1, array('name' => 'foo'));
	}

	public function testCanDeleteItem()
	{
		$model = Mockery::mock('Model', function ($mock) {
			$mock
				->shouldReceive('delete')->once()->andReturn(true);
		});

		$eloquent = Mockery::mock('Eloquent', function ($mock) use ($model) {
			$mock
				->shouldReceive('hasTrait')->andReturn(false)
				->shouldReceive('findOrFail')->once()->with(1, null)->andReturn($model);
		});

		$repository = new DummyRepository($eloquent);
		$this->assertTrue($repository->delete(1));
	}

	public function testCanGetPaginatedItems()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('paginate')->once()->with(25)->andReturn(true);
		});

		$repository = new DummyRepository($eloquent);
		$this->assertTrue($repository->getPaginated(25));
	}

	public function testCanGetAllItems()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('get')->once()->andReturn(true);
		});

		$repository = new DummyRepository($eloquent);
		$this->assertTrue($repository->all());
	}

	public function testCanGetAllPaginated()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('paginate')->once()->with(25)->andReturn(true);
		});

		$repository = new DummyRepository($eloquent);
		$this->assertTrue($repository->all(25));
	}

	public function testCanFindEntryBySlug()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('hasTrait')->once()->with('Sluggable')->andReturn(true);
			$mock->shouldReceive('whereSlug->firstOrFail')->andReturn('Model1');
		});

		$repository = new DummyRepository($eloquent);
		$this->assertEquals('Model1', $repository->find('foobar'));
	}

	public function testStringsWithNumbersAreRecognized()
	{
		$eloquent = Mockery::mock('Eloquent', function ($mock) {
			$mock->shouldReceive('hasTrait')->once()->with('Sluggable')->andReturn(true);
			$mock->shouldReceive('whereSlug->firstOrFail')->andReturn('Model1');
		});

		$repository = new DummyRepository($eloquent);
		$this->assertEquals('Model1', $repository->find('700-men'));
	}
}
