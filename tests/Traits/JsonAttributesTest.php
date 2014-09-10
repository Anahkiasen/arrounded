<?php
namespace Arrounded\Traits;

use Arrounded\ArroundedTestCase;
use Arrounded\Dummies\DummyJsonModel;

class JsonAttributesTest extends ArroundedTestCase
{
	public function testCanGetAndSetJsonAttributes()
	{
		$schedule = array('foo' => 'bar', 'baz' => 'qux');
		$model    = new DummyJsonModel(array(
			'schedule' => $schedule,
		));

		$this->assertEquals($schedule, $model->schedule);
		$this->assertEquals('{"foo":"bar","baz":"qux"}', $model->getAttributes()['schedule']);
	}

	public function testCanHaveDefaultsForJsonAttribute()
	{
		$model = new DummyJsonModel();

		$this->assertEquals(array(
				'facebook' => true,
				'twitter'  => array('foo' => false, 'bar' => true),
			), $model->notifications);

		$model->notifications = array('twitter' => array('foo' => true));
		$this->assertEquals(array(
				'facebook' => true,
				'twitter'  => array('foo' => true, 'bar' => true),
			), $model->notifications);
	}

	public function testDefaultsDontReplaceExistingAttributes()
	{
		$model = new DummyJsonModel(['notifications' => ['foo' => false]]);
		$model->setNotificationsAttribute(['bar' => true, 'qux' => false], ['foo' => true, 'qux' => true]);

		$this->assertFalse($model->notifications['foo']);
	}
}
