<?php
namespace Arrounded\Dummies;

use Arrounded\Traits\JsonAttributes;

class DummyJsonModel extends DummyModel
{
    use JsonAttributes;

    public function getNotificationsAttribute()
    {
        return $this->getJsonAttribute('notifications', [
            'facebook' => true,
            'twitter'  => ['foo' => false, 'bar' => true],
        ]);
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
        $this->setJsonAttribute('schedule', $schedule, [], true);
    }
}
