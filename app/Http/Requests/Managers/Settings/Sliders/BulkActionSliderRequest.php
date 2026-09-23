<?php

namespace App\Http\Requests\Managers\Settings\Sliders;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'sliders.delete' : 'sliders.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:sliders,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'sliders',
        ];
    }
}
