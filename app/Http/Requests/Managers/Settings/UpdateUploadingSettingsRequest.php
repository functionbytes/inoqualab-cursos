<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUploadingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'max_file_size' => ['required', 'integer', 'min:1', 'max:102400'],
            'max_files_per_upload' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_file_types' => ['required', 'array', 'min:1'],
            'allowed_file_types.*' => ['string'],
            'allowed_image_types' => ['required', 'array', 'min:1'],
            'allowed_image_types.*' => ['string'],
            'allowed_document_types' => ['required', 'array', 'min:1'],
            'allowed_document_types.*' => ['string'],
            'storage_driver' => ['required', 'string', 'in:local,s3,spaces,ftp'],
            's3_bucket' => ['nullable', 'string', 'max:255'],
            's3_region' => ['nullable', 'string', 'max:255'],
            'enable_virus_scan' => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_file_size.required' => 'El tamaño máximo por archivo es obligatorio.',
            'max_file_size.max' => 'El tamaño máximo por archivo no puede superar los 102400 KB (100 MB).',
            'max_files_per_upload.required' => 'La cantidad de archivos por carga es obligatoria.',
            'max_files_per_upload.max' => 'No se pueden permitir más de 50 archivos por carga.',
            'allowed_file_types.required' => 'Selecciona al menos una extensión general permitida.',
            'allowed_file_types.min' => 'Selecciona al menos una extensión general permitida.',
            'allowed_image_types.required' => 'Selecciona al menos una extensión de imagen permitida.',
            'allowed_image_types.min' => 'Selecciona al menos una extensión de imagen permitida.',
            'allowed_document_types.required' => 'Selecciona al menos una extensión de documento permitida.',
            'allowed_document_types.min' => 'Selecciona al menos una extensión de documento permitida.',
            'storage_driver.in' => 'El driver de almacenamiento seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'max_file_size' => 'tamaño máximo por archivo',
            'max_files_per_upload' => 'archivos por carga',
            'allowed_file_types' => 'archivos generales permitidos',
            'allowed_image_types' => 'imágenes permitidas',
            'allowed_document_types' => 'documentos permitidos',
            'storage_driver' => 'driver de almacenamiento',
            's3_bucket' => 'bucket',
            's3_region' => 'región',
            'enable_virus_scan' => 'escaneo de virus',
        ];
    }
}
