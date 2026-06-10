<?php

namespace App\Mail\Distributors\Inscriptions;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportsMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $enterprise;

    public string $email;

    public string $firstname;

    public string $lastname;

    public string $identification;

    public string $course;

    public string $date;

    public function __construct($inscription)
    {
        $this->enterprise = $inscription->user->relation->enterprise->title ?? '';
        $this->email = $inscription->user->relation->enterprise->email ?? '';
        $this->identification = $inscription->user->identification ?? '';
        $this->firstname = $inscription->user->firstname ?? '';
        $this->lastname = $inscription->user->lastname ?? '';
        $this->course = $inscription->course->title;
        $this->date = humanize_date($inscription->created_at);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('inscriptions.report', [
            'ENTERPRISE_NAME' => $this->enterprise,
            'CUSTOMER_NAMES' => ucwords($this->firstname).' '.ucwords($this->lastname),
            'CUSTOMER_IDENTIFICATION' => $this->identification,
            'COURSE_NAME' => $this->course,
            'DATE' => $this->date,
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
