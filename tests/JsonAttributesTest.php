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
}

// Dummies
//////////////////////////////////////////////////////////////////////

class DummyJsonModel extends Illuminate\Database\Eloquent\Model
{
	use JsonAttributes;

	protected $guarded = array();

	public function getScheduleAttribute()
	{
		return $this->getJsonAttribute('schedule');
	}

	public function setScheduleAttribute($schedule)
	{
		$this->setJsonAttribute('schedule', $schedule);
	}
}