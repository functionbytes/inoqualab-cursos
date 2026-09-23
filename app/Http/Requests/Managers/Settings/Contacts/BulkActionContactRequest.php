<?php

namespace App\Http\Requests\Managers\Settings\Contacts;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'contacts.delete' : 'contacts.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:reviewed,pending,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:contacts,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'contactos',
        ];
    }
}
