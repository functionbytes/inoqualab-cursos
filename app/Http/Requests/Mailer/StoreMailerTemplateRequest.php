<?php

namespace App\Http\Requests\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class StoreMailerTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.create');
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100', 'regex:/^[A-Z][A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'layout_id' => ['nullable', 'exists:mailer_layouts,id'],
            'module' => ['required', 'string', 'in:core,orders,notifications'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'La clave es obligatoria.',
            'key.regex' => 'La clave debe comenzar con mayúscula y solo contener letras mayúsculas, números y guiones bajos.',
            'name.required' => 'El nombre es obligatorio.',
            'subject.required' => 'El asunto es obligatorio.',
        ];
    }
}
