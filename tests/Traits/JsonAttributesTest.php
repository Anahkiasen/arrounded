<?php
namespace Arrounded\Traits;

use Arrounded\ArroundedTestCase;
use Arrounded\Dummies\DummyModel;
use Arrounded\Traits\JsonAttributes;

class JsonAttributesTest extends ArroundedTestCase
{
	public function testCanGetAndSetJsonAttributes()
	{
		$schedule = array('foo' => 'bar', 'baz' => 'qux');
		$model = new DummyJsonModel(array(
			'schedule' => $schedule,
		));

		$this->assertEquals($schedule, $model->schedule);
		$this->assertEquals('{"foo":"bar","baz":"qux"}', $model->getAttributes()['schedule']);
	}

	public function testCanHaveDefaultsForJsonAttribute()
	{
		$notifications = array('foo' => 'bar', 'baz' => 'qux');
		$model = new DummyJsonModel;

		$this->assertEquals(array('facebook' => true, 'twitter' => array('foo' => false, 'bar' => true)), $model->notifications);

		$model->notifications = array('twitter' => array('foo' => true));
		$this->assertEquals(array('facebook' => true, 'twitter' => array('foo' => true, 'bar' => true)), $model->notifications);
	}
}

// Dummies
//////////////////////////////////////////////////////////////////////

class DummyJsonModel extends DummyModel
{
	use JsonAttributes;

	public function getNotificationsAttribute()
	{
		return $this->getJsonAttribute('notifications', array(
			'facebook' => true,
			'twitter'  => array('foo' => false, 'bar' => true),
		));
	}

	public function setNotificationsAttribute($notifications)
	{
		$this->setJsonAttribute('notifications', $notifications);
	}

	public function getScheduleAttribute()
	{
		return $this->getJsonAttribute('schedule');
	}

	public function setScheduleAttribute($schedule)
	{
		$this->setJsonAttribute('schedule', $schedule);
	}
}
