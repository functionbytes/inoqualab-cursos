<?php

namespace App\Http\Requests\Managers\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la lista es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 500 caracteres.',
        ];
    }
}
