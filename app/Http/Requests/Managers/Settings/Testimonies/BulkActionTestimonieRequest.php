<?php

namespace App\Http\Requests\Managers\Settings\Testimonies;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionTestimonieRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'testimonies.delete' : 'testimonies.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:testimonies,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'testimonios',
        ];
    }
}
