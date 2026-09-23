<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdatePixelSettingsRequest;

class PixelSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.pixel.setting')->with([
            'metaPixelEnable' => setting('meta_pixel_enable') === 'true',
            'metaPixelId' => setting('meta_pixel_id', ''),
        ]);
    }

    public function update(UpdatePixelSettingsRequest $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        updateSettings([
            'meta_pixel_enable' => $request->has('meta_pixel_enable') ? 'true' : 'false',
            'meta_pixel_id' => $request->input('meta_pixel_id', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el pixel correctamente',
        ]);
    }
}
