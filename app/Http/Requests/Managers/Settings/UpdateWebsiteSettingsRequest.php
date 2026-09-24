<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebsiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        // El valor compone el nombre del parcial (course-card/{a,b,c,d}),
        // así que solo se aceptan esas cuatro letras.
        return [
            'pages_course_card_variant' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
            // Elige la vista del detalle: 1 = courses.view, 2 = courses.view-editorial.
            'pages_course_detail_variant' => ['required', Rule::in(['1', '2'])],
            // Elige la vista de /about: 1 = about (original), a/b/c/d = about/{informe,petri,norma,combinada}.
            'pages_about_variant' => ['required', Rule::in(['1', 'a', 'b', 'c', 'd'])],
        ];
    }

    public function messages(): array
    {
        return [
            'pages_course_card_variant.required' => 'Selecciona un diseño para la tarjeta de curso.',
            'pages_course_card_variant.in' => 'El diseño de la tarjeta de curso no es válido.',
            'pages_course_detail_variant.required' => 'Selecciona una modalidad para el detalle de curso.',
            'pages_course_detail_variant.in' => 'La modalidad del detalle de curso no es válida.',
            'pages_about_variant.required' => 'Selecciona un diseño para la página Sobre nosotros.',
            'pages_about_variant.in' => 'El diseño de la página Sobre nosotros no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'pages_course_card_variant' => 'diseño de la tarjeta de curso',
            'pages_course_detail_variant' => 'modalidad del detalle de curso',
            'pages_about_variant' => 'diseño de Sobre nosotros',
        ];
    }
}
