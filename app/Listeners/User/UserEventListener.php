<?php

namespace App\Listeners\User;

use App\Events\Auth\UserCreated;
use App\Events\Auth\UserDeactivated;
use App\Events\Auth\UserDeleted;
use App\Events\Auth\UserPasswordChanged;
use App\Events\Auth\UserReactivated;
use App\Events\Auth\UserUpdated;
use Illuminate\Support\Facades\Log;

class UserEventListener
{
    /**
     * Contexto mínimo de auditoría para cualquier evento con ->user: sin esto,
     * el log era un string fijo sin id/email, inútil para reconstruir qué pasó.
     */
    private function context($event): array
    {
        $user = $event->user ?? null;

        return [
            'user_id' => $user->id ?? null,
            'email' => $user->email ?? null,
            'actor_id' => auth()->id(),
        ];
    }

    public function onCreated($event)
    {
        Log::info('User Created', $this->context($event));
    }

    public function onUpdated($event)
    {
        Log::info('User Updated', $this->context($event));
    }

    public function onDeleted($event)
    {
        Log::info('User Deleted', $this->context($event));
    }

    public function onPasswordChanged($event)
    {
        Log::info('User Password Changed', $this->context($event));
    }

    public function onDeactivated($event)
    {
        Log::info('User Deactivated', $this->context($event));
    }

    public function onReactivated($event)
    {
        Log::info('User Reactivated', $this->context($event));
    }

    public function subscribe($events)
    {
        $events->listen(UserCreated::class, [self::class, 'onCreated']);
        $events->listen(UserUpdated::class, [self::class, 'onUpdated']);
        $events->listen(UserDeleted::class, [self::class, 'onDeleted']);
        $events->listen(UserPasswordChanged::class, [self::class, 'onPasswordChanged']);
        $events->listen(UserDeactivated::class, [self::class, 'onDeactivated']);
        $events->listen(UserReactivated::class, [self::class, 'onReactivated']);
    }
}
