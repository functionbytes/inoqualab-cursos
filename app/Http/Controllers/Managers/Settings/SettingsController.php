<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateGeneralSettingsRequest;
use App\Models\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        // Setting::key() devuelve el Builder (no null) cuando no hay match, por
        // el fallback `$scope(...) ?? $this` de Eloquent; firstOrCreate() evita
        // el 500 (`getMedia` no existe en Builder) si la fila aun no existe.
        $logo = Setting::firstOrCreate(['key' => 'page_logo'])->getMedia('logo')->count() > 0;
        $favicon = Setting::firstOrCreate(['key' => 'page_favicon'])->getMedia('favicon')->count() > 0;

        return view('managers.views.settings.settings.setting')->with([
            'logo' => $logo,
            'favicon' => $favicon,
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request): JsonResponse
    {
        $exp = ["<p class='ql-align-justify'><br></p>", '<p> </p>', '<p></p>', '<p></p>'];

        $data['page_title'] = $request->page_title;
        $data['page_copyright'] = $request->page_copyright;
        $data['page_email'] = $request->page_email;
        $data['page_phone'] = $request->page_phone;
        $data['page_cellphone'] = $request->page_cellphone;
        $data['page_whatsapp'] = $request->page_whatsapp;
        $data['page_description'] = str_replace($exp, '', $request->page_description);
        $data['page_politic'] = str_replace($exp, '', $request->page_politic);
        $data['page_term'] = str_replace($exp, '', $request->page_term);
        $data['page_address'] = $request->page_address;
        $data['page_map'] = $request->page_map;
        $data['social_media_facebook'] = $request->social_media_facebook;
        $data['social_media_instagram'] = $request->social_media_instagram;
        $data['social_media_twitter'] = $request->social_media_twitter;
        $data['social_media_youtube'] = $request->social_media_youtube;
        $data['social_media_linkedin'] = $request->social_media_linkedin;
        $data['page_hour_weekend'] = $request->page_hour_weekend;
        $data['page_hour_weekends'] = $request->page_hour_weekends;
        $data['reviews_enabled'] = $request->reviews_enabled == 1 ? 1 : 0;
        $data['contact_notifications'] = $request->contact_notifications == 1 ? 1 : 0;
        $data['registration_enabled'] = $request->registration_enabled == 1 ? 1 : 0;
        $data['aula_version'] = in_array((string) $request->aula_version, ['1', '2'], true) ? (string) $request->aula_version : '1';
        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }

    public function getLogo($slack)
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        return $this->getSettingMedia($slack, 'logo');
    }

    public function storeLogo(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        return $this->storeSettingMedia($request, 'logo');
    }

    public function deleteLogo($id)
    {
        return $this->deleteMedia($id);
    }

    public function getFavicon($slack)
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        return $this->getSettingMedia($slack, 'favicon');
    }

    public function storeFavicon(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        return $this->storeSettingMedia($request, 'favicon');
    }

    public function deleteFavicon($id)
    {
        return $this->deleteMedia($id);
    }

    private function getSettingMedia(string $slack, string $collection): JsonResponse
    {
        $setting = Setting::key($slack);
        $images = [];

        foreach ($setting->getMedia($collection) as $media) {
            $images[] = [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'name' => $media->name,
                'file' => $media->file_name,
                'path' => $media->getFullUrl(),
                'size' => $media->size,
            ];
        }

        return response()->json($images);
    }

    private function storeSettingMedia(Request $request, string $collection): JsonResponse
    {
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $setting = Setting::key($request->setting);
            $setting->addMediaFromRequest('file')->toMediaCollection($collection);

            return response()->json(['status' => 'success', 'setting' => $setting->key]);
        }

        return response()->json(['status' => 'error', 'message' => 'Archivo no válido.'], 422);
    }

    private function deleteMedia(int $id): JsonResponse
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        Media::where('id', $id)
            ->where('model_type', Setting::class)
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }
}
