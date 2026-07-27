<?php

namespace App\Mail\Pages\Contact;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Encolado: ContactsController lo envía con Mail::send() dentro de la petición
 * del visitante que rellena el formulario. Sin ShouldQueue, ese visitante
 * esperaba a que respondiera el SMTP, y si fallaba el correo se perdía con solo
 * una línea en el log. Encolado, la respuesta es inmediata y los fallos se
 * reintentan.
 */
class ResponseMails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $email;

    public string $date;

    public function __construct($contact)
    {
        $this->firstname = $contact->firstname;
        $this->email = $contact->email;
        $this->date = $contact->created_at ?? now();
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('contacts.response', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'CONTACT_DATE' => humanize_date($this->date),
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
