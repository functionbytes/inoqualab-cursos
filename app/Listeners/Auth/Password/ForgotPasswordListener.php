<?php

namespace App\Listeners\Auth\Password;

use App\Events\Auth\Password\ForgotPasswordCreated;
use App\Mail\Auth\Password\ForgotPasswordMails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ForgotPasswordListener
{
    public function handle(ForgotPasswordCreated $event): void
    {
        $this->handleMailForgotPassword($event);
    }

    public function handleMailForgotPassword(ForgotPasswordCreated $event)
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
}
