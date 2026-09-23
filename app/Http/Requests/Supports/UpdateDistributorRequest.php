<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Sin esta validacion, un campo faltante (address/cellphone/nit/email/
            // leading/supporting/available son NOT NULL en BD) lanza un 500 crudo
            // de MySQL en vez de un 422 con mensaje claro.
            'slack' => ['required', 'string', 'exists:distributors,slack'],
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'address' => ['required', 'string', 'min:3', 'max:100'],
            'cellphone' => ['required', 'string', 'min:6', 'max:10'],
            'nit' => ['required', 'string', 'min:6', 'max:100'],
            'email' => ['required', 'email'],
            'leading' => ['required', 'string', 'min:3', 'max:100'],
            'supporting' => ['required', 'string', 'min:3', 'max:100'],
            'available' => ['required', 'in:0,1'],
            'enterprise_generate' => ['nullable', 'in:0,1'],
        ];
    }
}
