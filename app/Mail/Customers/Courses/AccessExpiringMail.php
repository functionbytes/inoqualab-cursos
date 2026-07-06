<?php

namespace App\Mail\Customers\Courses;

use App\Models\Inscription;
use App\Services\MailTemplateService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Recordatorio de acceso por vencer. Se envía cuando el acceso del alumno a un
 * curso (inscriptions.enroll_expire) está por caducar sin haberlo completado,
 * para que lo termine o renueve. Mismo patrón que RenewalMail: plantilla editable
 * en el panel (mail_templates) renderizada por MailTemplateService.
 */
class AccessExpiringMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $toEmail;

    public string $courseTitle;

    public string $expiryDate;

    public string $renewUrl;

    public string $catalogUrl;

    public function __construct(Inscription $inscription)
    {
        $this->firstname = $inscription->user->firstname ?? '';
        $this->toEmail = $inscription->user->email ?? '';
        $this->courseTitle = $inscription->course->title ?? 'tu curso';
        $this->expiryDate = $inscription->enroll_expire
            ? Carbon::parse($inscription->enroll_expire)->format('d/m/Y')
            : '';
        $this->renewUrl = $inscription->course
            ? route('checkout', ['course', $inscription->course->slack])
            : route('courses');
        $this->catalogUrl = route('courses');
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('inscriptions.access_expiring', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'COURSE_TITLE' => $this->courseTitle,
            'EXPIRY_DATE' => $this->expiryDate,
            'RENEW_URL' => $this->renewUrl,
            'CATALOG_URL' => $this->catalogUrl,
        ]);

        return $this->to($this->toEmail)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
