<?php

namespace App\Http\Requests\Managers\MailTemplates;

use Illuminate\Foundation\Http\FormRequest;

class SendTestMailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'test_email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_email.required' => 'El correo de prueba es obligatorio.',
            'test_email.email' => 'El correo de prueba no es válido.',
            'test_email.max' => 'El correo no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'test_email' => 'correo de prueba',
        ];
    }
}
