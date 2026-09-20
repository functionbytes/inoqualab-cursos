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
class AlertsMails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $lastname;

    public string $email;

    public string $cellphone;

    public string $message;

    public string $date;

    public string $slack;

    public string $contactUrl;

    public function __construct($contact)
    {
        $this->slack = $contact->slack;
        $this->email = $contact->email;
        $this->firstname = $contact->firstname;
        $this->lastname = $contact->lastname;
        $this->cellphone = $contact->cellphone ?? '';
        $this->message = $contact->message ?? '';
        $this->date = $contact->created_at ?? now();
        $this->contactUrl = route('manager.contacts.edit', $contact->slack);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('contacts.alert', [
            'CONTACT_ID' => $this->slack,
            'CONTACT_DATE' => humanize_date($this->date),
            'CONTACT_NAMES' => ucwords($this->firstname).' '.ucwords($this->lastname),
            'CONTACT_EMAIL' => $this->email,
            'CONTACT_PHONE' => $this->cellphone ?: '—',
            // El mensaje viaja crudo desde un formulario público: MailTemplateService
            // solo hace str_replace (no hay Blade/autoescape de por medio), así que
            // se escapa aquí -- sin esto, un remitente podría inyectar HTML/script
            // en el panel de correo del manager.
            'CONTACT_MESSAGE' => nl2br(e($this->message)),
            'CONTACT_URL' => $this->contactUrl,
        ]);

        // settings es key/value: setting('page_email'). getSetting()->page_email
        // devolvía null → la alerta de contacto se enviaba a un destinatario vacío.
        return $this->to(setting('page_email'))
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
