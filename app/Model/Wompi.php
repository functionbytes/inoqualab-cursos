<?php

namespace App\Model;

use App\Services\WompiService;

class Wompi
{
    public string $public;

    public string $currency = 'COP';

    public string $reference;

    public int $amount;

    public string $signature;

    public string $expiration;

    public string $redirect;

    public string $firstname;

    public string $lastname;

    public string $email;

    public string $cellphone;

    public function __construct($total, $slack, $user)
    {
        $service = new WompiService;

        $amountInCents = (int) ($total * 100);

        $this->public = $service->getPublicKey();
        $this->reference = $slack;
        $this->amount = $amountInCents;
        $this->currency = 'COP';
        // La expiración debe calcularse ANTES de la firma para que coincida con data-expiration-time.
        $this->expiration = $service->defaultExpiration();
        $this->signature = $service->generateIntegritySignature($slack, $amountInCents, 'COP', $this->expiration);
        $this->redirect = route('payments.response');
        $this->firstname = $user->firstname ?? '';
        $this->lastname = $user->lastname ?? '';
        $this->email = $user->email ?? '';
        $this->cellphone = $user->cellphone ?? '';
    }
}
