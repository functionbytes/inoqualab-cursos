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
            'description.max' => 'El testimonio no puede superar los 2000 caracteres.',
            'available.in' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'description' => 'testimonio',
            'available' => 'estado',
        ];
    }
}
