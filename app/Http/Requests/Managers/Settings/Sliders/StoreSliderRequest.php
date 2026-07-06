<?php

namespace App\Http\Requests\Managers\Settings\Sliders;

use Illuminate\Foundation\Http\FormRequest;

class StoreSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sliders.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'position' => ['nullable', 'integer', 'min:1'],
            'available' => ['nullable', 'in:0,1'],
            'ubication' => ['nullable', 'in:1,2'],
            'url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'subtitle.max' => 'El subtítulo no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',
            'position.integer' => 'La posición debe ser un número entero.',
            'position.min' => 'La posición debe ser mayor o igual a 1.',
            'available.in' => 'El estado no es válido.',
            'ubication.in' => 'La ubicación no es válida.',
            'url.max' => 'El enlace no puede superar los 2048 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'subtitle' => 'subtítulo',
            'description' => 'descripción',
            'position' => 'posición',
            'available' => 'estado',
            'ubication' => 'ubicación',
            'url' => 'enlace',
        ];
    }
}
