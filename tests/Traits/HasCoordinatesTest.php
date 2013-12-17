<?php
use Arrounded\Traits\HasCoordinates;

class HasCoordinatesTest extends ArroundedTests
{
	use HasCoordinates;

	public static function setUpBeforeClass()
	{
		$cache = Mockery::mock('alias:Cache');
		$cache->shouldReceive('rememberForever')->andReturnUsing(function($hash, $closure) {
			return $closure();
		});
		$cache->shouldReceive('forget');
	}

	public function testCanFindCoordinatesOfAddress()
	{
		$address     = '134 boulevard Valbenoite';
		$coordinates = $this->getCoordinates($address);

		$this->assertEquals(array(
			'lat' => 45.422672,
			'lng' => 4.3980133,
		), $coordinates);
	}

	public function testCanFindCoordinatesOfMultipleComponents()
	{
		$coordinates = $this->getCoordinates(array(
			'622 Treat Avenue',
			'San Fransisco',
			'94110'
		));

		$this->assertEquals(array(
			'lat' => 37.7601369,
			'lng' => -122.4137406,
		), $coordinates);
	}

	public function testCanUpdateCoordinatesWithAttributes()
	{
		$model = new DummyCoordinatesModel(array(
			'address' => null,
			'lat'     => 2,
			'lng'     => 2,
		));

		$model->address = '134 boulevard Valbenoite';

		$this->assertEquals(45.422672, $model->lat);
		$this->assertEquals(4.3980133, $model->lng);
	}
}

// Dummies
//////////////////////////////////////////////////////////////////////

class DummyCoordinatesModel extends DummyModel
{
	use HasCoordinates;

	public function setAddressAttribute($address)
	{
		$this->setAttributeWithCoordinates('address', $address);
	}
}