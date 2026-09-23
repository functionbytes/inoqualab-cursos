<?php

namespace App\Http\Requests\Managers\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class AddNewsletterListMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
        ];
    }
}
