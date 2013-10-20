<?php
use Arrounded\Traits\HasCoordinates;

class HasCoordinatesTest extends ArroundedTests
{
	use HasCoordinates;

	public function testCanFindCoordinatesOfAddress()
	{
		$address     = '134 boulevard Valbenoite';
		$coordinates = $this->getCoordinates($address);

		$this->assertEquals(array(
			'lat' => 57.935938,
			'lng' => 15.4915967,
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
			'lat' => 23.5990934,
			'lng' => 120.4703258
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

		$this->assertEquals(57.935938, $model->lat);
		$this->assertEquals(15.4915967, $model->lng);
	}
}

// Dummies
//////////////////////////////////////////////////////////////////////

class DummyCoordinatesModel extends Illuminate\Database\Eloquent\Model
{
	use HasCoordinates;

	protected $guarded = array();

	public function setAddressAttribute($address)
	{
		$this->setAttributeWithCoordinates('address', $address);
	}
}