<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'address' => ['required', 'string', 'min:3', 'max:100'],
            'cellphone' => ['required', 'string', 'min:6', 'max:10'],
            'nit' => ['required', 'string', 'min:6', 'max:100'],
            'email' => ['required', 'email'],
            'leading' => ['required', 'string', 'min:3', 'max:100'],
            'supporting' => ['required', 'string', 'min:3', 'max:100'],
            'enterprise_generate' => ['nullable', 'in:0,1'],
        ];
    }
}
