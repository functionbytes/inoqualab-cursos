<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * El portal Customers no usa permisos Spatie (ver .claude/rules): está
     * protegido por scoping de propiedad (`user_id`/`app('customer')`), no
     * por `panel.permission` — 'customer' en RolesAndPermissionsSeeder no
     * tiene alias `reviews.*`. Comprobar un permiso aquí sería inconsistente
     * con el resto del dominio, no una protección real adicional.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course.required' => 'El curso es obligatorio.',
            'rating.required' => 'Selecciona una calificación.',
            'rating.min' => 'La calificación debe ser de 1 a 5 estrellas.',
            'rating.max' => 'La calificación debe ser de 1 a 5 estrellas.',
            'comment.max' => 'El comentario no puede superar los 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'course' => 'curso',
            'rating' => 'calificación',
            'comment' => 'comentario',
        ];
    }
}
