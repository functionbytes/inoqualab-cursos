<?php

namespace App\Mail\Auth\Password;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMails extends Mailable
{
    use Queueable, SerializesModels;

    protected string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('auth.reset_password_success', [
            'CUSTOMER_EMAIL' => $this->email,
            'LOGIN_URL' => route('login'),
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
