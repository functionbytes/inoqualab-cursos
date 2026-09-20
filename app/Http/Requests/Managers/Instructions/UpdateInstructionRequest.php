<?php

namespace App\Http\Requests\Managers\Instructions;

use Illuminate\Foundation\Http\FormRequest;

/** Compartido con el panel de soporte (mismo permiso `instructions.*`). */
class UpdateInstructionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('instructions.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:instructions,slack'],
            'title' => ['required', 'string', 'max:191'],
            'short' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'available' => ['required', 'boolean'],
            'categorie' => ['required', 'integer', 'exists:instruction_categories,id'],
            'tags' => ['nullable', 'string', 'max:45'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La instrucción es obligatoria.',
            'slack.exists' => 'La instrucción no existe.',
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 191 caracteres.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
            'categorie.required' => 'La categoría es obligatoria.',
            'categorie.exists' => 'La categoría seleccionada no existe.',
            'tags.max' => 'Las etiquetas no pueden superar los 45 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'instrucción',
            'title' => 'título',
            'short' => 'resumen',
            'description' => 'descripción',
            'available' => 'estado',
            'categorie' => 'categoría',
            'tags' => 'etiquetas',
        ];
    }
}
