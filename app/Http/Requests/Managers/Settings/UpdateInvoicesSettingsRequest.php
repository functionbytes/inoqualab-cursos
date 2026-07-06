<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoicesSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'invoices_notification_email_enable' => ['nullable'],
            'invoice_default' => ['nullable', 'string', 'max:255'],
            'invoice_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_default.max' => 'El consecutivo de facturación no puede superar los 255 caracteres.',
            'invoice_days.integer' => 'El tiempo de facturación debe ser un número entero.',
            'invoice_days.min' => 'El tiempo de facturación no puede ser negativo.',
            'invoice_days.max' => 'El tiempo de facturación no puede superar los 365 días.',
        ];
    }

    public function attributes(): array
    {
        return [
            'invoices_notification_email_enable' => 'habilitar correo CC',
            'invoice_default' => 'consecutivo de facturación',
            'invoice_days' => 'tiempo de facturación',
        ];
    }
}
