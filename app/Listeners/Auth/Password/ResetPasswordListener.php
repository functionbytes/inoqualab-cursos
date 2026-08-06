<?php

namespace App\Listeners\Auth\Password;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Mail\Auth\Password\ResetPasswordMails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetPasswordListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(ResetPasswordCreated $event): void
    {
        $this->handleMailResetPassword($event);
    }

    public function handleMailResetPassword(ResetPasswordCreated $event): void
    {
        $user = $event->user;
        $email = $user->email;

        $mail = new ResetPasswordMails($email);
        Mail::to($email)->queue($mail);
    }

    public function failed(ResetPasswordCreated $event, \Throwable $exception): void
    {
        Log::error('Listener failed: '.static::class, [
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
