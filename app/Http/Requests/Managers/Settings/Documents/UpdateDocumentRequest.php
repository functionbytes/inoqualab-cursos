<?php

namespace App\Http\Requests\Managers\Settings\Documents;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('documents.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:documents,slack'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'available' => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El documento es obligatorio.',
            'slack.exists' => 'El documento seleccionado no existe.',
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',
            'available.in' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'documento',
            'title' => 'título',
            'description' => 'descripción',
            'available' => 'estado',
        ];
    }
}
