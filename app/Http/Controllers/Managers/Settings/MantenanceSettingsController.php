<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateMaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Str;

class MantenanceSettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        // Asegura que exista una llave persistida antes de pintar la vista:
        // la vista NUNCA recibe el valor real (se revela via AJAX) para no
        // exponerlo en texto plano en el HTML de la respuesta.
        $this->currentSecret();

        return view('managers.views.settings.maintenance.setting');
    }

    /**
     * Devuelve la llave secreta vigente. Se consume via AJAX desde el botón
     * "Revelar"/"Copiar" — nunca se imprime directamente en el HTML de index().
     */
    public function secret(): JsonResponse
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        return response()->json([
            'value' => $this->currentSecret(),
        ]);
    }

    /**
     * Devuelve la llave secreta persistida, generando y guardando una nueva
     * solo la primera vez (nunca se regenera en cada GET).
     */
    private function currentSecret(): string
    {
        $secret = setting('maintenance_mode_value');

        if (blank($secret)) {
            $secret = Str::random(32);
            updateSettings(['maintenance_mode_value' => $secret]);
        }

        return $secret;
    }

    public function update(UpdateMaintenanceRequest $request)
    {
        if ($request->maintenance_mode == 'true') {

            $data['maintenance_mode'] = $request->maintenance_mode;
            $data['maintenance_mode_value'] = $request->maintenance_mode_value;
            updateSettings($data);
            Artisan::call('down', ['--secret' => $request->maintenance_mode_value]);

        } else {

            $data['maintenance_mode'] = $request->maintenance_mode;
            $data['maintenance_mode_value'] = null;
            updateSettings($data);
            Artisan::call('up');

        }

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó correctamente el modo mantenimiento',
        ]);

    }
}
