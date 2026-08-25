<?php

use App\Models\Setting\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * `settings.key` solo tenía un índice normal, así que nada impedía repetir una
 * clave. Once claves habían acabado duplicadas, y eso rompe los ajustes de una
 * forma silenciosa y difícil de ver:
 *
 *   - Al escribir, updateSettings() hace Setting::updateOrCreate(['key' => …]),
 *     que actualiza la PRIMERA fila que encuentra (el id más bajo).
 *   - Al leer, _settingsCache() hace pluck('value', 'key'), y con claves
 *     repetidas gana la ÚLTIMA fila (el id más alto).
 *
 * Guardar el ajuste desde el panel no cambiaba, por tanto, lo que la aplicación
 * leía después. En esta base 'meta_description' llevaba así desde el 7-ago: el
 * panel mostraba el texto correcto mientras la portada publicaba a los
 * buscadores el resto de una prueba de XSS guardada en las filas nuevas.
 *
 * Se conserva la fila que tenga media asociada (logo, favicon y demás cuelgan
 * de un Setting vía Spatie) y, si no hay ninguna con media, la de menor id: es
 * la canónica, la que el panel viene editando. El índice único impide que
 * vuelva a pasar.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('settings')
            ->select('key')
            ->groupBy('key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key');

        $removed = [];

        foreach ($duplicates as $key) {
            $rows = DB::table('settings')->where('key', $key)->orderBy('id')->get(['id', 'value']);

            $withMedia = DB::table('media')
                ->where('model_type', Setting::class)
                ->whereIn('model_id', $rows->pluck('id'))
                ->orderBy('model_id')
                ->value('model_id');

            $keepId = $withMedia ?? $rows->first()->id;

            $discard = $rows->where('id', '!=', $keepId);

            // Si alguna descartada arrastraba media (dos filas con adjuntos), se
            // reasigna a la superviviente antes de borrarla: perder el logo por
            // una limpieza de duplicados sería mucho peor que el duplicado.
            DB::table('media')
                ->where('model_type', Setting::class)
                ->whereIn('model_id', $discard->pluck('id'))
                ->update(['model_id' => $keepId]);

            DB::table('settings')->whereIn('id', $discard->pluck('id'))->delete();

            $removed[$key] = [
                'kept' => $keepId,
                'deleted' => $discard->pluck('id')->all(),
            ];
        }

        if ($removed !== []) {
            // Queda en el log porque descarta datos: si algún valor superviviente
            // resulta no ser el que se esperaba, aquí está qué se borró.
            Log::warning('Deduplicación de settings antes de imponer la clave única', $removed);
        }

        Schema::table('settings', function (Blueprint $table) {
            // El índice normal previo sobra: el único ya sirve para buscar.
            $table->dropIndex('settings_key_index');
            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->index('key', 'settings_key_index');
        });

        // Las filas duplicadas NO se restauran: eran precisamente el problema y
        // el log de up() deja constancia de cuáles se borraron.
    }
};
