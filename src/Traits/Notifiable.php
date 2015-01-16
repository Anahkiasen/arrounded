<?php
namespace Arrounded\Traits;

trait Notifiable
{
    /**
     * Checks if the user wants to be notified of something
     *
     * @param string $notification
     *
     * @return bool
     */
    public function notifiedOn($notification)
    {
        return (bool) array_get($this->notifications, $notification);
    }

    /**
     * Serialize and store notifications settings
     *
     * @param array $notifications
     */
    public function setNotificationsAttribute($notifications)
    {
        $this->setJsonAttribute('notifications', $notifications);
    }

    /**
     * Unserialize and return notifications settings
     *
     * @return array
     */
    public function getNotificationsAttribute()
    {
        return $this->getJsonAttribute('notifications');
    }
}
