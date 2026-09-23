<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class SubmitIndexNowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'urls' => ['required', 'array', 'min:1', 'max:50'],
            'urls.*' => ['url'],
        ];
    }

    public function attributes(): array
    {
        return [
            'urls' => 'URLs',
        ];
    }
}
