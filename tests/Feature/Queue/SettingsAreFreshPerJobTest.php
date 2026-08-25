<?php

namespace Tests\Feature\Queue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Cada trabajo de cola tiene que arrancar con los ajustes que hay en la base.
 *
 * Los cachés de ajustes (`_settingsCache`, `_settingModelCache`) guardan en
 * variables `static`, que viven lo que viva el proceso PHP. En web eso es
 * inocuo —un proceso por petición—, pero el worker corre con `--max-jobs=500`:
 * procesa cientos de trabajos sin reiniciarse. Un ajuste cambiado desde el
 * panel no llegaba a esos trabajos; seguían usando la foto leída al arrancar,
 * y no había forma de notarlo salvo esperar a que el worker se reciclara.
 *
 * Concretamente: apagar las notificaciones de factura no las apagaba en el
 * worker, que seguía enviando correos durante los siguientes 499 trabajos.
 */
class SettingsAreFreshPerJobTest extends TestCase
{
    use RefreshDatabase;

    /** Dispara el evento que Laravel emite justo antes de ejecutar un trabajo. */
    private function simulateNextJob(): void
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('payload')->andReturn([]);
        $job->shouldReceive('resolveName')->andReturn('trabajo-de-prueba');
        $job->shouldReceive('getJobId')->andReturn('1');

        event(new JobProcessing('redis', $job));
    }

    public function test_a_setting_changed_elsewhere_reaches_the_next_job(): void
    {
        DB::table('settings')->insert(['key' => 'interruptor_de_prueba', 'value' => '1']);
        forgetSettingsCache();

        $this->assertEquals(1, setting('interruptor_de_prueba'));

        // Otro proceso (el panel) lo apaga: escribe en la base sin tocar este caché.
        DB::table('settings')->where('key', 'interruptor_de_prueba')->update(['value' => '0']);

        $this->assertEquals(
            1,
            setting('interruptor_de_prueba'),
            'Dentro del mismo trabajo el valor cacheado se mantiene, que es lo que se espera.'
        );

        $this->simulateNextJob();

        $this->assertEquals(
            0,
            setting('interruptor_de_prueba'),
            'El siguiente trabajo tiene que ver el valor nuevo; si no, el worker sirve ajustes obsoletos hasta reiniciarse.'
        );
    }

    public function test_forget_clears_the_model_cache_too(): void
    {
        DB::table('settings')->insert(['key' => 'page_logo', 'value' => 'antes']);
        forgetSettingsCache();

        $this->assertSame('antes', _settingModelCache('page_logo')?->value);

        DB::table('settings')->where('key', 'page_logo')->update(['value' => 'despues']);

        $this->assertSame('antes', _settingModelCache('page_logo')?->value, 'Sigue cacheado.');

        forgetSettingsCache();

        $this->assertSame(
            'despues',
            _settingModelCache('page_logo')?->value,
            'El caché de modelos Setting (logo, favicon, meta_image) también tiene que olvidarse.'
        );
    }
}
