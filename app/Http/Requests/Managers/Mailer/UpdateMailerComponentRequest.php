<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailerComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:layout,header,footer,component'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_enabled' => ['boolean'],
            'is_protected' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'type.required' => 'El tipo es obligatorio.',
            'type.in' => 'El tipo debe ser layout, header, footer o component.',
            'group_name.max' => 'El nombre del grupo no puede superar los 100 caracteres.',
            'subject.max' => 'El asunto no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'type' => 'tipo',
            'group_name' => 'nombre del grupo',
            'subject' => 'asunto',
            'content' => 'contenido',
            'is_enabled' => 'estado',
            'is_protected' => 'protegido',
        ];
    }
}
