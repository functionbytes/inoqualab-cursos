<?php

namespace App\Http\Requests\Enterprises;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.update');
    }

    public function rules(): array
    {
        return [
            'supporting' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:191'],
            'cellphone' => ['nullable', 'string', 'max:191'],
            'email' => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('enterprises', 'email')->ignore(app('enterprise')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.max' => 'El correo electrónico no puede superar los 191 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado en nuestro sistema.',
            'supporting.max' => 'El campo de soporte no puede superar los 191 caracteres.',
            'address.max' => 'La dirección no puede superar los 191 caracteres.',
            'cellphone.max' => 'El celular no puede superar los 191 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'supporting' => 'soporte',
            'address' => 'dirección',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
        ];
    }
}
