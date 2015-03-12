<?php
namespace Arrounded\Traits;

use Arrounded\Dummies\DummyJsonModel;
use Arrounded\TestCases\ArroundedTestCase;

class JsonAttributesTest extends ArroundedTestCase
{
    public function testCanGetAndSetJsonAttributes()
    {
        $schedule = ['foo' => 'bar', 'baz' => 'qux'];
        $model    = new DummyJsonModel([
            'schedule' => $schedule,
        ]);

        $this->assertEquals($schedule, $model->schedule);
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $model->getAttributes()['schedule']);
    }

    public function testCanHaveDefaultsForJsonAttribute()
    {
        $model = new DummyJsonModel();

        $this->assertEquals([
            'facebook' => true,
            'twitter'  => ['foo' => false, 'bar' => true],
        ], $model->notifications);

        $model->notifications = ['twitter' => ['foo' => true]];
        $this->assertEquals([
            'facebook' => true,
            'twitter'  => ['foo' => true, 'bar' => true],
        ], $model->notifications);
    }

    public function testDefaultsDontReplaceExistingAttributes()
    {
        $model = new DummyJsonModel(['notifications' => ['foo' => false]]);
        $model->setNotificationsAttribute(['bar'               => true, 'qux' => false], ['foo' => true, 'qux' => true]);

        $this->assertFalse($model->notifications['foo']);
    }
}
