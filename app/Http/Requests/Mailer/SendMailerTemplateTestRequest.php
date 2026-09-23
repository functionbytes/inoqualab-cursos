<?php

namespace App\Http\Requests\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class SendMailerTemplateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'test_email' => ['required', 'email'],
        ];
    }

    public function attributes(): array
    {
        return [
            'test_email' => 'correo de prueba',
        ];
    }
}
