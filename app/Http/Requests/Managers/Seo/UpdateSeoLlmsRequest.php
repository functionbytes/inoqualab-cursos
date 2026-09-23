<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoLlmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
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
