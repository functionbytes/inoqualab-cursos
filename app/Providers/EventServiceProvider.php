<?php

namespace App\Providers;

use App\Events\Auth\Password\ForgotPasswordCreated;
use App\Events\Auth\Password\ResetPasswordCreated;
use App\Events\Inscriptions\InscriptionCreated;
use App\Events\Invoices\InvoiceCreated;
use App\Listeners\Auth\Password\ForgotPasswordListener;
use App\Listeners\Auth\Password\ResetPasswordListener;
use App\Listeners\Auth\UserEventListener as AuthUserEventListener;
use App\Listeners\Inscriptions\InscriptionListener;
use App\Listeners\Inscriptions\RemoveFromRemarketingLists;
use App\Listeners\Invoices\InvoiceListener;
use App\Listeners\LogMailSent;
use App\Listeners\User\UserEventListener as UserUserEventListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        AuthUserEventListener::class,
        UserUserEventListener::class,
    ];

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        InscriptionCreated::class => [
            InscriptionListener::class,
            RemoveFromRemarketingLists::class,
        ],
        InvoiceCreated::class => [
            InvoiceListener::class,
        ],
        ForgotPasswordCreated::class => [
            ForgotPasswordListener::class,
        ],
        ResetPasswordCreated::class => [
            ResetPasswordListener::class,
        ],
        MessageSent::class => [
            LogMailSent::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
