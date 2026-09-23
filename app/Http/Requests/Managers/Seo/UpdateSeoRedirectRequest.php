<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_regex' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'is_active' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'source_path' => 'ruta origen',
            'target_path' => 'ruta destino',
            'status_code' => 'código de estado',
            'note' => 'nota',
        ];
    }
}
