<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailerVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            // key/module son editables en el form para variables no-sistema
            // (edit.blade.php solo los deshabilita si is_system) pero antes no
            // estaban en las reglas: FormRequest::validated() descarta
            // cualquier input fuera de rules(), así que el cambio se perdía en
            // silencio -- el usuario veía "actualizada" pero la clave/módulo
            // seguían igual. Mismas reglas que Store para mantener el formato.
            'key' => ['required', 'string', 'max:100', 'regex:/^[A-Z][A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'example_value' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'module' => ['required', 'string', 'max:50'],
            'is_enabled' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'La clave es obligatoria.',
            'key.max' => 'La clave no puede superar los 100 caracteres.',
            'key.regex' => 'La clave debe comenzar con una letra mayúscula y contener solo letras mayúsculas, números y guiones bajos.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 500 caracteres.',
            'example_value.max' => 'El valor de ejemplo no puede superar los 255 caracteres.',
            'category.required' => 'La categoría es obligatoria.',
            'category.max' => 'La categoría no puede superar los 50 caracteres.',
            'module.required' => 'El módulo es obligatorio.',
            'module.max' => 'El módulo no puede superar los 50 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'key' => 'clave',
            'name' => 'nombre',
            'description' => 'descripción',
            'example_value' => 'valor de ejemplo',
            'category' => 'categoría',
            'module' => 'módulo',
            'is_enabled' => 'estado',
        ];
    }
}
