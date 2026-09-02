<?php

namespace App\Http\Requests\Managers\Settings\Testimonies;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('testimonies.create');
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'regex:/^(fas|far|fab) fa-[a-z0-9-]+$/'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'benefit' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'integer', 'min:0'],
            'counter_value' => ['nullable', 'string', 'max:20'],
            'counter_suffix' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:2000'],
            'available' => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'El nombre es obligatorio.',
            'firstname.max' => 'El nombre no puede superar los 100 caracteres.',
            'lastname.required' => 'El apellido es obligatorio.',
            'lastname.max' => 'El apellido no puede superar los 100 caracteres.',
            'role.max' => 'El rol no puede superar los 100 caracteres.',
            'icon.regex' => 'El ícono debe ser una clase de Font Awesome válida, ej: fas fa-star.',
            'rating.integer' => 'La calificación debe ser un número.',
            'rating.min' => 'La calificación mínima es 1.',
            'rating.max' => 'La calificación máxima es 5.',
            'benefit.max' => 'El resultado destacado no puede superar los 150 caracteres.',
            'counter_value.max' => 'El número del contador no puede superar los 20 caracteres.',
            'counter_suffix.max' => 'El sufijo del contador no puede superar los 10 caracteres.',
            'description.max' => 'El testimonio no puede superar los 2000 caracteres.',
            'available.in' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'role' => 'rol',
            'icon' => 'ícono',
            'rating' => 'calificación',
            'benefit' => 'resultado destacado',
            'counter_value' => 'número del contador',
            'counter_suffix' => 'sufijo del contador',
            'description' => 'testimonio',
            'available' => 'estado',
        ];
    }
}
