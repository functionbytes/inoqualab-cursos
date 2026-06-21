<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'imap_host' => ['nullable', 'string', 'max:255'],
            'imap_port' => ['nullable', 'integer', 'between:1,65535'],
            'imap_protocol' => ['nullable', 'string', 'in:imap,pop3'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:255'],
            'imap_encryption' => ['nullable', 'string', 'in:ssl,tls,notls'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,starttls'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'imap_port.between' => 'El puerto IMAP debe estar entre 1 y 65535.',
            'imap_protocol.in' => 'El protocolo IMAP debe ser imap o pop3.',
            'imap_encryption.in' => 'El cifrado IMAP debe ser ssl, tls o notls.',
            'mail_port.between' => 'El puerto de correo debe estar entre 1 y 65535.',
            'mail_encryption.in' => 'El cifrado de correo debe ser tls, ssl o starttls.',
            'mail_from_address.email' => 'El correo remitente no tiene un formato válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'imap_host' => 'servidor IMAP',
            'imap_port' => 'puerto IMAP',
            'mail_host' => 'servidor SMTP',
            'mail_port' => 'puerto SMTP',
            'mail_from_address' => 'correo remitente',
            'mail_from_name' => 'nombre remitente',
        ];
    }
}
