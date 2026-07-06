<?php

namespace App\Mail\Pages\Contact;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertsMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $lastname;

    public string $email;

    public string $date;

    public string $slack;

    public string $contactUrl;

    public function __construct($contact)
    {
        $this->slack = $contact->slack;
        $this->email = $contact->email;
        $this->firstname = $contact->firstname;
        $this->lastname = $contact->lastname;
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
            'CONTACT_URL' => $this->contactUrl,
        ]);

        // settings es key/value: setting('page_email'). getSetting()->page_email
        // devolvía null → la alerta de contacto se enviaba a un destinatario vacío.
        return $this->to(setting('page_email'))
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
