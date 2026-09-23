<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class ImportSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'update_existing' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'csv_file' => 'archivo CSV',
            'update_existing' => 'actualizar existentes',
        ];
    }
}
