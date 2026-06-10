<?php

namespace App\Listeners\User;

use App\Events\Auth\UserConfirmed;
use App\Events\Auth\UserCreated;
use App\Events\Auth\UserDeactivated;
use App\Events\Auth\UserDeleted;
use App\Events\Auth\UserPasswordChanged;
use App\Events\Auth\UserPermanentlyDeleted;
use App\Events\Auth\UserReactivated;
use App\Events\Auth\UserRestored;
use App\Events\Auth\UserSocialDeleted;
use App\Events\Auth\UserUnconfirmed;
use App\Events\Auth\UserUpdated;
use Illuminate\Support\Facades\Log;

class UserEventListener
{
    public function onCreated($event)
    {
        Log::info('User Created');
    }

    public function onUpdated($event)
    {
        Log::info('User Updated');
    }

    public function onDeleted($event)
    {
        Log::info('User Deleted');
    }

    public function onConfirmed($event)
    {
        Log::info('User Confirmed');
    }

    public function onUnconfirmed($event)
    {
        Log::info('User Unconfirmed');
    }

    public function onPasswordChanged($event)
    {
        Log::info('User Password Changed');
    }

    public function onDeactivated($event)
    {
        Log::info('User Deactivated');
    }

    public function onReactivated($event)
    {
        Log::info('User Reactivated');
    }

    public function onSocialDeleted($event)
    {
        Log::info('User Social Deleted');
    }

    public function onPermanentlyDeleted($event)
    {
        Log::info('User Permanently Deleted');
    }

    public function onRestored($event)
    {
        Log::info('User Restored');
    }

    public function subscribe($events)
    {
        $events->listen(UserCreated::class, [self::class, 'onCreated']);
        $events->listen(UserUpdated::class, [self::class, 'onUpdated']);
        $events->listen(UserDeleted::class, [self::class, 'onDeleted']);
        $events->listen(UserConfirmed::class, [self::class, 'onConfirmed']);
        $events->listen(UserUnconfirmed::class, [self::class, 'onUnconfirmed']);
        $events->listen(UserPasswordChanged::class, [self::class, 'onPasswordChanged']);
        $events->listen(UserDeactivated::class, [self::class, 'onDeactivated']);
        $events->listen(UserReactivated::class, [self::class, 'onReactivated']);
        $events->listen(UserSocialDeleted::class, [self::class, 'onSocialDeleted']);
        $events->listen(UserPermanentlyDeleted::class, [self::class, 'onPermanentlyDeleted']);
        $events->listen(UserRestored::class, [self::class, 'onRestored']);
    }
}
