<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnalyticsSettingsController extends Controller
{
    public function index(): View
    {
        $credentialsInfo = $this->getCredentialsInfo();

        return view('managers.views.settings.analytics.index', [
            'settings' => $this->currentSettings(),
            'credentialsInfo' => $credentialsInfo,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'google_analytics_property_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
            'google_analytics_measurement_id' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]+$/i'],
            'analytics_cache_lifetime' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'meta_pixel_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
            'microsoft_clarity_id' => ['nullable', 'string', 'max:20'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:50'],
            'linkedin_insight_tag_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
        ]);

        if ($request->filled('google_analytics_credentials')) {
            $json = trim($request->google_analytics_credentials);
            $decoded = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return response()->json(['success' => false, 'message' => 'Las credenciales no son JSON válido.'], 422);
            }

            if (($decoded['type'] ?? '') !== 'service_account') {
                return response()->json(['success' => false, 'message' => 'El JSON debe ser de tipo service_account.'], 422);
            }

            Storage::put('analytics/service-account-credentials.json', $json);
        }

        $data = [
            'google_analytics_enable' => $request->has('google_analytics_enable') ? 'true' : 'false',
            'google_analytics_property_id' => $request->input('google_analytics_property_id', ''),
            'google_analytics_measurement_id' => $request->input('google_analytics_measurement_id', ''),
            'analytics_cache_lifetime' => $request->input('analytics_cache_lifetime', 60),
            'meta_pixel_id' => $request->input('meta_pixel_id', ''),
            'microsoft_clarity_id' => $request->input('microsoft_clarity_id', ''),
            'tiktok_pixel_id' => $request->input('tiktok_pixel_id', ''),
            'linkedin_insight_tag_id' => $request->input('linkedin_insight_tag_id', ''),
        ];

        updateSettings($data);

        return response()->json(['success' => true, 'message' => 'Configuración guardada correctamente.']);
    }

    private function currentSettings(): array
    {
        return [
            'google_analytics_enable' => setting('google_analytics_enable') === 'true',
            'google_analytics_property_id' => setting('google_analytics_property_id', ''),
            'google_analytics_measurement_id' => setting('google_analytics_measurement_id', ''),
            'analytics_cache_lifetime' => (int) setting('analytics_cache_lifetime', 60),
            'meta_pixel_id' => setting('meta_pixel_id', ''),
            'microsoft_clarity_id' => setting('microsoft_clarity_id', ''),
            'tiktok_pixel_id' => setting('tiktok_pixel_id', ''),
            'linkedin_insight_tag_id' => setting('linkedin_insight_tag_id', ''),
        ];
    }

    private function getCredentialsInfo(): array
    {
        if (! Storage::exists('analytics/service-account-credentials.json')) {
            return ['configured' => false];
        }

        try {
            $data = json_decode(Storage::get('analytics/service-account-credentials.json'), true);

            return [
                'configured' => true,
                'project_id' => $data['project_id'] ?? '—',
                'client_email' => $data['client_email'] ?? '—',
            ];
        } catch (\Exception) {
            return ['configured' => false];
        }
    }
}
