<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdatePixelSettingsRequest;

class PixelSettingsController extends Controller
{
    public function index()
    {

        return view('managers.views.settings.pixel.setting')->with([
        ]);

    }

    public function update(UpdatePixelSettingsRequest $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        $data['fb_pixel_enable'] = $request->fb_pixel_enable;
        $data['fb_pixel'] = $request->fb_pixel;

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo el pixel correctamente',
        ]);

    }
}
