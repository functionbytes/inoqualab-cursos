<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsLlmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'llms_txt' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'llms_txt' => 'contenido llms.txt',
        ];
    }
}
