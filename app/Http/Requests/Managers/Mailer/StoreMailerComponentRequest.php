<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class StoreMailerComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:100', 'unique:mailer_layouts,alias', 'regex:/^[a-z0-9_]+$/'],
            'code' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:layout,header,footer,component'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_enabled' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'alias.required' => 'El alias es obligatorio.',
            'alias.max' => 'El alias no puede superar los 100 caracteres.',
            'alias.unique' => 'Ya existe un componente con este alias.',
            'alias.regex' => 'El alias solo puede contener letras minúsculas, números y guiones bajos.',
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
            'alias' => 'alias',
            'type' => 'tipo',
            'group_name' => 'nombre del grupo',
            'subject' => 'asunto',
            'content' => 'contenido',
            'is_enabled' => 'estado',
        ];
    }
}
