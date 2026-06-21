<?php

namespace App\Http\Requests\Managers\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('analytics.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'email' => ['required', 'email', 'max:255'],
            'format' => ['required', 'in:pdf,excel,csv'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'frequency.required' => 'La frecuencia es obligatoria.',
            'frequency.in' => 'La frecuencia debe ser diaria, semanal o mensual.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'format.required' => 'El formato es obligatorio.',
            'format.in' => 'El formato debe ser PDF, Excel o CSV.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'frequency' => 'frecuencia',
            'email' => 'correo electrónico',
            'format' => 'formato',
        ];
    }
}
