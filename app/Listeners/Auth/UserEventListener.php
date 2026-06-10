<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserConfirmed;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Events\Auth\UserRegistered;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserEventListener
{
    public function onLoggedIn($event)
    {
        $ip_address = request()->getClientIp();

        $event->user->fill([
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => $ip_address,
        ]);

        $event->user->save();

        Log::info('User Logged In: '.$event->user->full_name);
    }

    public function onLoggedOut($event)
    {
        Log::info('User Logged Out: '.$event->user->full_name);
    }

    public function onRegistered($event)
    {
        Log::info('User Registered: '.$event->user->full_name);
    }

    public function onConfirmed($event)
    {
        Log::info('User Confirmed: '.$event->user->full_name);
    }

    public function subscribe($events)
    {
        $events->listen(UserLoggedIn::class, [self::class, 'onLoggedIn']);
        $events->listen(UserLoggedOut::class, [self::class, 'onLoggedOut']);
        $events->listen(UserRegistered::class, [self::class, 'onRegistered']);
        $events->listen(UserConfirmed::class, [self::class, 'onConfirmed']);
    }
}
