<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class ClearSeo404LogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.delete');
    }

    public function rules(): array
    {
        return [
            'all' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'all' => 'todos',
        ];
    }
}
