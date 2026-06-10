<?php

namespace App\Http\Requests\Distributors\Inscriptions;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enterprise' => ['required', 'string'],
            'distributor' => ['required', 'string'],
            'course' => ['required', 'integer'],
            'user' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'enterprise.required' => 'La empresa es obligatoria.',
            'distributor.required' => 'El distribuidor es obligatorio.',
            'course.required' => 'El curso es obligatorio.',
            'user.required' => 'El usuario es obligatorio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'enterprise' => 'empresa',
            'distributor' => 'distribuidor',
            'course' => 'curso',
            'user' => 'usuario',
        ];
    }
}
