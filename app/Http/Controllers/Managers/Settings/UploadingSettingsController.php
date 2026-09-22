<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateUploadingSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UploadingSettingsController extends Controller
{
    private const GENERAL_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar'];

    private const IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];

    private const DOCUMENT_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods'];

    public function index(): View
    {
        $settings = [
            'max_file_size' => (int) setting('uploading_max_file_size', 10240),
            'max_files_per_upload' => (int) setting('uploading_max_files_per_upload', 10),
            'allowed_file_types' => json_decode(setting('uploading_allowed_file_types', '[]'), true) ?: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'allowed_image_types' => json_decode(setting('uploading_allowed_image_types', '[]'), true) ?: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'allowed_document_types' => json_decode(setting('uploading_allowed_document_types', '[]'), true) ?: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
            'storage_driver' => setting('uploading_storage_driver', 'local') ?: 'local',
            's3_bucket' => setting('uploading_s3_bucket', ''),
            's3_region' => setting('uploading_s3_region', ''),
            'enable_virus_scan' => settingEnabled('uploading_enable_virus_scan', false),
        ];

        $phpLimits = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ];

        return view('managers.views.settings.system.uploading', [
            'settings' => $settings,
            'phpLimits' => $phpLimits,
            'generalTypes' => self::GENERAL_TYPES,
            'imageTypes' => self::IMAGE_TYPES,
            'documentTypes' => self::DOCUMENT_TYPES,
        ]);
    }

    public function update(UpdateUploadingSettingsRequest $request): RedirectResponse
    {
        updateSettings([
            'uploading_max_file_size' => $request->input('max_file_size'),
            'uploading_max_files_per_upload' => $request->input('max_files_per_upload'),
            'uploading_allowed_file_types' => json_encode($request->input('allowed_file_types', [])),
            'uploading_allowed_image_types' => json_encode($request->input('allowed_image_types', [])),
            'uploading_allowed_document_types' => json_encode($request->input('allowed_document_types', [])),
            'uploading_storage_driver' => $request->input('storage_driver'),
            'uploading_s3_bucket' => $request->input('s3_bucket', ''),
            'uploading_s3_region' => $request->input('s3_region', ''),
            'uploading_enable_virus_scan' => $request->input('enable_virus_scan'),
        ]);

        return redirect()
            ->route('manager.settings.system.uploading')
            ->with('success', 'Configuración de carga de archivos guardada correctamente.');
    }
}
