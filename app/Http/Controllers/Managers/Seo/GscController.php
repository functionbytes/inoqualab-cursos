<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GscController extends Controller
{
    public function index(GoogleSearchConsoleService $service): View
    {
        $status = [
            'configured' => $service->isConfigured(),
            'connected' => $service->isConnected(),
            'property_url' => config('services.gsc.property_url', config('app.url')),
        ];

        return view('managers.views.seo.gsc.index', compact('status'));
    }

    public function connect(GoogleSearchConsoleService $service): RedirectResponse
    {
        if (! $service->isConfigured()) {
            return redirect()->route('manager.seo.gsc.index')
                ->with('error', 'Configura GSC_CLIENT_ID y GSC_CLIENT_SECRET en .env antes de conectar.');
        }

        return redirect()->away($service->authorizationUrl(csrf_token()));
    }

    public function callback(Request $request, GoogleSearchConsoleService $service): RedirectResponse
    {
        // Validar el parámetro `state` (anti-CSRF de OAuth): debe coincidir con
        // el token de sesión que connect() envió a Google. Sin esto, un tercero
        // podría forzar la conexión de una cuenta de Search Console ajena.
        if (! hash_equals(csrf_token(), (string) $request->input('state'))) {
            return redirect()->route('manager.seo.gsc.index')
                ->with('error', 'Estado de autorización inválido. Reintenta la conexión.');
        }

        if (! $request->filled('code')) {
            return redirect()->route('manager.seo.gsc.index')
                ->with('error', 'Google no devolvió un código de autorización.');
        }

        $ok = $service->handleCallback((string) $request->input('code'));

        return redirect()->route('manager.seo.gsc.index')
            ->with($ok ? 'success' : 'error', $ok
                ? 'Conectado a Google Search Console correctamente.'
                : 'No se pudo canjear el código por un refresh token.');
    }

    public function disconnect(GoogleSearchConsoleService $service): RedirectResponse
    {
        $service->disconnect();

        return redirect()->route('manager.seo.gsc.index')
            ->with('success', 'Desconectado de Google Search Console.');
    }

    public function import(Request $request, GoogleSearchConsoleService $service): RedirectResponse
    {
        if (! $service->isConnected()) {
            return redirect()->route('manager.seo.gsc.index')
                ->with('error', 'No hay conexión activa con Google Search Console.');
        }

        $days = (int) $request->input('days', 28);
        $days = max(1, min(90, $days));

        $updated = $service->importIntoMetas($days);

        return redirect()->route('manager.seo.gsc.index')
            ->with('success', "Importadas {$updated} URLs desde Search Console (últimos {$days} días).");
    }
}
