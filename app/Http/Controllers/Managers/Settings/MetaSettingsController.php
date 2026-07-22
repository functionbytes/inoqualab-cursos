<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateMetaSettingsRequest;
use App\Models\Setting\Setting;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MetaSettingsController extends Controller
{
    public function index()
    {
        // Setting::key() devuelve el Builder (no null) cuando no hay match, por
        // el fallback `$scope(...) ?? $this` de Eloquent; firstOrCreate() evita
        // el 500 (`getMedia` no existe en Builder) si la fila aun no existe.
        $meta = Setting::firstOrCreate(['key' => 'meta_image'])->getMedia('meta')->count() > 0;

        return view('managers.views.settings.metadata.setting')->with([
            'metadata' => $meta,
        ]);

    }

    public function update(UpdateMetaSettingsRequest $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        $data['meta_title'] = $request->meta_title;
        $data['meta_description'] = $request->meta_description;
        $data['meta_keywords'] = $request->meta_keywords;
        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }

    public function storeMetas(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $setting = Setting::key($request->setting);
            $setting->addMediaFromRequest('file')->toMediaCollection('meta');

            return response()->json(['status' => 'success', 'setting' => $setting->slack]);
        }

    }

    public function deleteMetas($id)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        Media::where('id', $id)
            ->where('model_type', Setting::class)
            ->where('collection_name', 'meta')
            ->first()?->delete();

        return response()->json(['status' => 'success']);

    }

    public function getMetas($slack)
    {

        $setting = Setting::key($slack);

        $images = $setting->getMedia('meta')->map(function ($thumbnail) {
            return [
                'id' => $thumbnail->id,
                'uuid' => $thumbnail->uuid,
                'name' => $thumbnail->name,
                'file' => $thumbnail->file_name,
                'path' => $thumbnail->getFullUrl(),
                'size' => $thumbnail->size,
            ];
        });

        return response()->json($images);

    }
}
