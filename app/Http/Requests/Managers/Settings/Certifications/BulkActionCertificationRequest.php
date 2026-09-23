<?php

namespace App\Http\Requests\Managers\Settings\Certifications;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'certifications.delete' : 'certifications.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:certifications,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'certificados',
        ];
    }
}
