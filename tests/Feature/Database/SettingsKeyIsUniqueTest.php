<?php

namespace Tests\Feature\Database;

use App\Models\Setting\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * `settings.key` tiene que ser única.
 *
 * Sin esa restricción los ajustes se corrompen sin dar la cara: updateSettings()
 * escribe con updateOrCreate(['key' => …]), que toca la primera fila con esa
 * clave, mientras _settingsCache() lee con pluck('value', 'key'), donde gana la
 * última. Guardar desde el panel deja de tener efecto y nadie se entera.
 *
 * Pasó de verdad: 'meta_description' acabó con tres filas y la portada publicó
 * durante semanas los restos de una prueba de XSS mientras el panel mostraba el
 * texto correcto.
 */
class SettingsKeyIsUniqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_rejects_a_duplicated_key(): void
    {
        Setting::create(['key' => 'clave_de_prueba', 'value' => 'primero']);

        $this->expectException(QueryException::class);

        DB::table('settings')->insert(['key' => 'clave_de_prueba', 'value' => 'segundo']);
    }

    public function test_saving_a_setting_changes_what_the_app_reads(): void
    {
        updateSettings(['clave_de_prueba' => 'valor inicial']);
        _settingsCache(null, true);

        $this->assertSame('valor inicial', setting('clave_de_prueba'));

        updateSettings(['clave_de_prueba' => 'valor corregido']);
        _settingsCache(null, true);

        $this->assertSame(
            'valor corregido',
            setting('clave_de_prueba'),
            'Lo guardado tiene que ser lo que se lee: si divergen, hay claves duplicadas.'
        );

        $this->assertSame(1, DB::table('settings')->where('key', 'clave_de_prueba')->count());
    }

    public function test_no_duplicated_keys_remain(): void
    {
        $duplicates = DB::table('settings')
            ->select('key')
            ->groupBy('key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key');

        $this->assertSame([], $duplicates->all());
    }
}
