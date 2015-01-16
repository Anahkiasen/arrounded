<?php
namespace Arrounded\Traits;

use Hash;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\UserTrait;

/**
 * A model with Auth capabilities
 */
trait Authentifiable
{
    use UserTrait;
    use RemindableTrait;

    /**
     * Hash password before save
     *
     * @param string $password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }
}
