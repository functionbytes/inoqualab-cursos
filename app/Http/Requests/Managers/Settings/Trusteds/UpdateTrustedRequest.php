<?php

namespace App\Http\Requests\Managers\Settings\Trusteds;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrustedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('trusteds.update');
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:trusteds,id'],
            'title' => ['required', 'string', 'max:255'],
            'available' => ['nullable', 'in:0,1'],
            'url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El aliado es obligatorio.',
            'id.exists' => 'El aliado seleccionado no existe.',
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'available.in' => 'El estado no es válido.',
            'url.max' => 'El enlace no puede superar los 2048 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'aliado',
            'title' => 'título',
            'available' => 'estado',
            'url' => 'enlace',
        ];
    }
}
