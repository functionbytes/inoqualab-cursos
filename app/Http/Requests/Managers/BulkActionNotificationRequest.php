<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Son las notificaciones del propio usuario logueado: no hay permiso
        // Spatie que revisar, la relación ya scopea a Auth::user() en el controller.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:read,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'notificaciones',
        ];
    }
}
