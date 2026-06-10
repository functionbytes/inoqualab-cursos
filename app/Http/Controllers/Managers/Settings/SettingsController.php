<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting\Setting;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingsController extends Controller
{
    public function index()
    {

        $logo = Setting::key('page_logo')->getMedia('logo')->count() > 0 ? true : false;
        $favicon = Setting::key('page_favicon')->getMedia('favicon')->count() > 0 ? true : false;

        return view('managers.views.settings.settings.setting')->with([
            'logo' => $logo,
            'favicon' => $favicon,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'page_title' => ['required', 'string', 'max:255'],
            'page_email' => ['nullable', 'email', 'max:255'],
            'page_phone' => ['nullable', 'string', 'max:30'],
            'page_cellphone' => ['nullable', 'string', 'max:30'],
            'page_whatsapp' => ['nullable', 'string', 'max:30'],
            'page_address' => ['nullable', 'string', 'max:500'],
            'social_media_facebook' => ['nullable', 'url', 'max:500'],
            'social_media_instagram' => ['nullable', 'url', 'max:500'],
            'social_media_twitter' => ['nullable', 'url', 'max:500'],
            'social_media_youtube' => ['nullable', 'url', 'max:500'],
            'social_media_linkedin' => ['nullable', 'url', 'max:500'],
        ], [
            'page_title.required' => 'El título del sitio es obligatorio.',
            'page_email.email' => 'El correo de contacto no tiene un formato válido.',
            'social_media_facebook.url' => 'La URL de Facebook no es válida.',
            'social_media_instagram.url' => 'La URL de Instagram no es válida.',
            'social_media_twitter.url' => 'La URL de Twitter no es válida.',
            'social_media_youtube.url' => 'La URL de YouTube no es válida.',
            'social_media_linkedin.url' => 'La URL de LinkedIn no es válida.',
        ]);

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
        $data['newsletter_enabled'] = $request->newsletter_enabled == 1 ? 1 : 0;
        $data['contact_notifications'] = $request->contact_notifications == 1 ? 1 : 0;
        $data['registration_enabled'] = $request->registration_enabled == 1 ? 1 : 0;
        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }

    public function getLogo($slack)
    {

        $setting = Setting::key($slack);

        if ($setting->getMedia('logo')->count() > 0) {

            $thumbnails = $setting->getMedia('logo');

            foreach ($thumbnails as $thumbnail) {

                $images[] = [
                    'id' => $thumbnail->id,
                    'uuid' => $thumbnail->uuid,
                    'name' => $thumbnail->name,
                    'file' => $thumbnail->file_name,
                    'path' => $thumbnail->getfullUrl(),
                    'size' => $thumbnail->size,
                ];
            }

            return response()->json($images);
        }

        $images = [];

        return response()->json($images);

    }

    public function storeLogo(Request $request)
    {

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $setting = Setting::key($request->setting);
            $setting->addMediaFromRequest('file')->toMediaCollection('logo');

            return response()->json(['status' => 'success', 'setting' => $setting->key]);
        }

    }

    public function deleteLogo($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }

    public function getFavicon($slack)
    {

        $setting = Setting::key($slack);

        if ($setting->getMedia('favicon')->count() > 0) {

            $thumbnails = $setting->getMedia('favicon');

            foreach ($thumbnails as $thumbnail) {

                $images[] = [
                    'id' => $thumbnail->id,
                    'uuid' => $thumbnail->uuid,
                    'name' => $thumbnail->name,
                    'file' => $thumbnail->file_name,
                    'path' => $thumbnail->getfullUrl(),
                    'size' => $thumbnail->size,
                ];
            }

            return response()->json($images);
        }

        $images = [];

        return response()->json($images);

    }

    public function storeFavicon(Request $request)
    {

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $setting = Setting::key($request->setting);
            $setting->addMediaFromRequest('file')->toMediaCollection('favicon');

            return response()->json(['status' => 'success', 'setting' => $setting->key]);

        }
    }

    public function deleteFavicon($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }
}
