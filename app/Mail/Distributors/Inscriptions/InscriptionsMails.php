<?php

namespace App\Mail\Distributors\Inscriptions;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscriptionsMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $lastname;

    public string $email;

    public string $course;

    public string $start;

    public string $expire;

    public function __construct($inscription)
    {
        $this->firstname = $inscription->user->firstname ?? '';
        $this->lastname = $inscription->user->lastname ?? '';
        $this->email = $inscription->user->email;
        $this->course = $inscription->course->title;
        $this->start = $inscription->enroll_start;
        $this->expire = $inscription->enroll_expire;
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('inscriptions.notification', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'CUSTOMER_LASTNAME' => $this->lastname,
            'COURSE_NAME' => $this->course,
            'START_DATE' => $this->start,
            'EXPIRE_DATE' => $this->expire,
            'LOGIN_URL' => route('login'),
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
