<?php
use Arrounded\Traits\JsonAttributes;

class JsonAttributesTest extends ArroundedTests
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

		$this->assertEquals(array('facebook' => true, 'twitter' => false), $model->notifications);

		$model->notifications = array('twitter' => true);
		$this->assertEquals(array('facebook' => true, 'twitter' => true), $model->notifications);
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
			'twitter'  => false,
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
