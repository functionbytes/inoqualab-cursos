<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class StoreMailerEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:mailer_endpoints,slug', 'regex:/^[a-z0-9\-]+$/'],
            'source' => ['required', 'in:api,internal,webhook'],
            'type' => ['required', 'in:transactional,notification,marketing'],
            'description' => ['nullable', 'string'],
            'mailer_template_id' => ['nullable', 'exists:mailer_templates,id'],
            'expected_variables' => ['nullable', 'array'],
            'required_variables' => ['nullable', 'array'],
            'variable_mappings' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.max' => 'El slug no puede superar los 100 caracteres.',
            'slug.unique' => 'Ya existe un endpoint con este slug.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'source.required' => 'El origen es obligatorio.',
            'source.in' => 'El origen debe ser api, internal o webhook.',
            'type.required' => 'El tipo es obligatorio.',
            'type.in' => 'El tipo debe ser transactional, notification o marketing.',
            'mailer_template_id.exists' => 'La plantilla seleccionada no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'slug' => 'slug',
            'source' => 'origen',
            'type' => 'tipo',
            'description' => 'descripción',
            'mailer_template_id' => 'plantilla',
            'expected_variables' => 'variables esperadas',
            'required_variables' => 'variables requeridas',
            'variable_mappings' => 'mapeo de variables',
            'is_active' => 'estado',
        ];
    }
}
