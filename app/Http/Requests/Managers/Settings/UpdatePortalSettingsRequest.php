<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        // Solo 'a' o 'b': el valor se usa para componer el nombre de la vista
        // (index / index-b), así que cualquier otra cosa dejaría el portal en
        // pantalla de error.
        return [
            'customers_dashboard_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_courses_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_nav_layout' => ['required', Rule::in(['horizontal', 'vertical'])],
            'customers_certificates_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_orders_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_documents_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_settings_variant' => ['required', Rule::in(['a', 'b'])],
            'customers_notifications_variant' => ['required', Rule::in(['a', 'b'])],
            'aula_version' => ['required', Rule::in(['1', '2'])],
        ];
    }

    public function messages(): array
    {
        return [
            'customers_dashboard_variant.in' => 'La variante del panel de inicio no es válida.',
            'customers_courses_variant.in' => 'La variante de mis cursos no es válida.',
            'customers_nav_layout.in' => 'La posición del menú no es válida.',
            'customers_certificates_variant.in' => 'La variante de certificados no es válida.',
            'customers_orders_variant.in' => 'La variante de pedidos no es válida.',
            'customers_documents_variant.in' => 'La variante de documentos no es válida.',
            'customers_settings_variant.in' => 'La variante de configuración no es válida.',
            'customers_notifications_variant.in' => 'La variante de notificaciones no es válida.',
            'aula_version.in' => 'La variante del aula no es válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'customers_dashboard_variant' => 'variante del panel de inicio',
            'customers_courses_variant' => 'variante de mis cursos',
            'customers_nav_layout' => 'posición del menú',
            'customers_certificates_variant' => 'variante de certificados',
            'customers_orders_variant' => 'variante de pedidos',
            'customers_documents_variant' => 'variante de documentos',
            'customers_settings_variant' => 'variante de configuración',
            'customers_notifications_variant' => 'variante de notificaciones',
            'aula_version' => 'variante del aula',
        ];
    }
}
