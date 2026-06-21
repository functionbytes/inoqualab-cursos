<?php

namespace App\Listeners\Auth\Password;

use App\Events\Auth\Password\ForgotPasswordCreated;
use App\Mail\Auth\Password\ForgotPasswordMails;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ForgotPasswordListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(ForgotPasswordCreated $event): void
    {
        $this->handleMailForgotPassword($event);
    }

    public function handleMailForgotPassword(ForgotPasswordCreated $event): void
    {
        $user = $event->user;
        $email = $user->email;
        $slack = $user->slack;

        $url = URL::temporarySignedRoute(
            'password.reset.token',
            Carbon::now()->addHours(24),
            ['slack' => $slack]
        );

        $mail = new ForgotPasswordMails($email, $url);

        Mail::to($email)->queue($mail);
    }

    public function failed(ForgotPasswordCreated $event, \Throwable $exception): void
    {
        Log::error('Listener failed: '.static::class, ['error' => $exception->getMessage()]);
    }
}
