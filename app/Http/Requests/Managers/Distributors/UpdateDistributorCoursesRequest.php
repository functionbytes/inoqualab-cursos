<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorCoursesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('distributors.update');
    }

    /**
     * El select multiple envía los cursos como string separado por comas
     * (`"1,4,9"`); se normaliza a array antes de validar cada id.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'courses' => $this->filled('courses') ? explode(',', (string) $this->input('courses')) : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:distributors,slack'],
            'courses' => ['array'],
            'courses.*' => ['integer', 'exists:courses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El distribuidor es obligatorio.',
            'slack.exists' => 'El distribuidor indicado no existe.',
            'courses.*.integer' => 'El identificador del curso no es válido.',
            'courses.*.exists' => 'Uno de los cursos seleccionados no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'distribuidor',
            'courses' => 'cursos',
        ];
    }
}
