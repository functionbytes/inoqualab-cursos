<?php

namespace App\Mail\Customers\Certificates;

use App\Models\Users\Certificate;
use App\Services\MailTemplateService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RenewalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $toEmail;

    public string $courseTitle;

    public string $endAt;

    public string $renewUrl;

    public function __construct(Certificate $certificate)
    {
        $this->firstname = $certificate->user->firstname ?? '';
        $this->toEmail = $certificate->user->email ?? '';
        $this->courseTitle = $certificate->course->title ?? 'tu curso';
        $this->endAt = $certificate->end_at
            ? Carbon::parse($certificate->end_at)->format('d/m/Y')
            : '';
        $this->renewUrl = route('checkout', ['course', optional($certificate->course)->slack]);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('certificates.renewal', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'COURSE_TITLE' => $this->courseTitle,
            'EXPIRY_DATE' => $this->endAt,
            'RENEW_URL' => $this->renewUrl,
        ]);

        return $this->to($this->toEmail)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
