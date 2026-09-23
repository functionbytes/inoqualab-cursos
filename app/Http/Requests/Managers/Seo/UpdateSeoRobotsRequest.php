<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoRobotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'robots_txt' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'robots_txt' => 'contenido robots.txt',
        ];
    }
}
