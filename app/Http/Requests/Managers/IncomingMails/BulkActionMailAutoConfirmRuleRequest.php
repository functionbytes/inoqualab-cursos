<?php

namespace App\Http\Requests\Managers\IncomingMails;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionMailAutoConfirmRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'incoming-mails.delete' : 'incoming-mails.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:mail_auto_confirm_rules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Selecciona una acción.',
            'action.in' => 'La acción seleccionada no es válida.',
            'ids.required' => 'Selecciona al menos una regla.',
            'ids.min' => 'Selecciona al menos una regla.',
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'reglas',
        ];
    }
}
