<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class CreateRedirectFrom404Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'log_id' => ['required', 'integer', 'exists:seo_404_logs,id'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['nullable', 'in:301,302'],
        ];
    }

    public function attributes(): array
    {
        return [
            'log_id' => 'registro 404',
            'target_path' => 'ruta destino',
            'status_code' => 'código de estado',
        ];
    }
}
