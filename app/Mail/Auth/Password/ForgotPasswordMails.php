<?php

namespace App\Mail\Auth\Password;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;

    public string $url;

    public function __construct(string $email, string $url)
    {
        $this->email = $email;
        $this->url = $url;
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('auth.forgot_password', [
            'CUSTOMER_EMAIL' => $this->email,
            'RESET_URL' => $this->url,
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
