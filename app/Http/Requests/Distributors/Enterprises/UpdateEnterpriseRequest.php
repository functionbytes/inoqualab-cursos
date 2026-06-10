<?php

namespace App\Http\Requests\Distributors\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El nombre de la empresa es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'nombre de la empresa',
            'nit' => 'NIT',
            'email' => 'correo electrónico',
        ];
    }
}
