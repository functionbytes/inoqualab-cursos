<?php

namespace App\Listeners\Auth\Password;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Mail\Auth\Password\ResetPasswordMails;
use Illuminate\Support\Facades\Mail;

class ResetPasswordListener
{
    public function handle(ResetPasswordCreated $event): void
    {
        $this->handleMailResetPassword($event);
    }

    public function handleMailResetPassword(ResetPasswordCreated $event)
    {

        $user = $event->user;
        $email = $user->email;

        $mail = new ResetPasswordMails($user);
        Mail::to($email)->queue($mail);

    }
}
