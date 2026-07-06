<?php

namespace App\Http\Requests\Managers\Settings\Testimonies;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('testimonies.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:testimonies,slack'],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'available' => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El testimonio es obligatorio.',
            'slack.exists' => 'El testimonio seleccionado no existe.',
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
            'slack' => 'testimonio',
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'description' => 'testimonio',
            'available' => 'estado',
        ];
    }
}
