<?php

namespace Tests\Feature\Settings;

use App\Services\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los ajustes de tipo interruptor tienen que responder al panel.
 *
 * `setting()` normaliza los numéricos con `$value + 0`, así que un ajuste
 * guardado como '1' vuelve como **int** 1 y uno guardado como '0' como int 0.
 * Comparar en estricto contra la cadena —`=== '1'`, `!== '0'`— es entonces
 * siempre falso o siempre verdadero, y el interruptor deja de tener efecto sin
 * que nada falle ni se registre.
 *
 * Estaba roto en tres sitios a la vez: apagar el boletín no impedía las
 * suscripciones, su casilla en el panel seguía saliendo marcada (mostrando lo
 * contrario de lo guardado) e IndexNow no se activaba nunca. `settingEnabled()`
 * normaliza y admite un valor por defecto explícito.
 */
class SettingSwitchesTest extends TestCase
{
    use RefreshDatabase;

    private function store(array $values): void
    {
        updateSettings($values);
        _settingsCache(null, true);
    }

    public function test_a_numeric_switch_is_read_as_a_boolean(): void
    {
        $this->store(['interruptor' => '1']);
        $this->assertTrue(settingEnabled('interruptor'));

        $this->store(['interruptor' => '0']);
        $this->assertFalse(
            settingEnabled('interruptor'),
            "setting() devuelve int 0 para '0'; comparar en estricto contra la cadena fallaba aquí."
        );
    }

    public function test_textual_switches_are_understood(): void
    {
        $this->store(['interruptor' => 'true']);
        $this->assertTrue(settingEnabled('interruptor'));

        $this->store(['interruptor' => 'false']);
        $this->assertFalse(settingEnabled('interruptor'));
    }

    public function test_an_absent_or_unreadable_setting_uses_the_given_default(): void
    {
        _settingsCache(null, true);
        $this->assertTrue(settingEnabled('nunca_guardado', true));
        $this->assertFalse(settingEnabled('nunca_guardado', false));

        // Un valor corrupto no debe interpretarse a la ligera.
        $this->store(['interruptor' => 'quizás']);
        $this->assertTrue(settingEnabled('interruptor', true));
        $this->assertFalse(settingEnabled('interruptor', false));
    }

    public function test_disabling_the_newsletter_actually_rejects_subscriptions(): void
    {
        $this->store(['newsletter_enabled' => '0']);

        $this->postJson(route('newsletters.store'), ['email' => 'alguien@example.com'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_enabling_the_newsletter_does_not_reject_subscriptions(): void
    {
        $this->store(['newsletter_enabled' => '1']);

        $this->postJson(route('newsletters.store'), ['email' => 'alguien@example.com'])
            ->assertStatus(200);
    }

    public function test_disabling_the_popup_returns_no_content(): void
    {
        $this->store(['newsletter_enabled' => '1', 'newsletter_popup_enabled' => '0']);

        $this->get(route('newsletters.ajax-popup'))->assertNoContent();
    }

    public function test_indexnow_only_reports_enabled_when_it_is_switched_on(): void
    {
        $service = app(IndexNowService::class);

        $this->store(['seo_indexnow_enabled' => '1', 'seo_indexnow_key' => 'clave-de-prueba']);
        $this->assertTrue($service->enabled(), 'Activado en el panel, el servicio tiene que activarse.');

        $this->store(['seo_indexnow_enabled' => '0']);
        $this->assertFalse($service->enabled());
    }

    public function test_indexnow_stays_off_without_a_key(): void
    {
        $this->store(['seo_indexnow_enabled' => '1', 'seo_indexnow_key' => '']);

        $this->assertFalse(app(IndexNowService::class)->enabled());
    }

    public function test_disabling_registration_blocks_the_public_signup(): void
    {
        $this->store(['registration_enabled' => '0']);

        $this->post(route('register'), [
            'email' => 'nuevo@example.com',
            'password' => 'Contrasena-2026-larga',
            'password_confirmation' => 'Contrasena-2026-larga',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);
    }

    public function test_registration_is_enabled_by_default(): void
    {
        _settingsCache(null, true);

        $this->assertTrue(
            settingEnabled('registration_enabled', true),
            'Sin el ajuste guardado el registro tiene que seguir abierto.'
        );
    }

    /**
     * Guardarraíl: prohíbe volver a comparar setting() en estricto contra una
     * cadena numérica. Es la forma exacta en que se rompieron los tres
     * interruptores, y no da ningún síntoma: la comparación simplemente sale
     * siempre igual. `settingEnabled()` o `==` son las alternativas correctas.
     */
    public function test_no_strict_comparison_between_setting_and_a_numeric_string(): void
    {
        $offenders = [];

        foreach ([app_path(), resource_path('views')] as $root) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

            foreach ($files as $file) {
                if (! $file->isFile() || ! preg_match('/\.(php|blade\.php)$/', $file->getFilename())) {
                    continue;
                }

                foreach (file($file->getPathname()) as $number => $line) {
                    if (preg_match("/setting\([^)]*\)\s*(===|!==)\s*'[0-9]+'|'[0-9]+'\s*(===|!==)\s*setting\(/", $line)) {
                        $offenders[] = sprintf(
                            '%s:%d  %s',
                            str_replace(base_path().'/', '', $file->getPathname()),
                            $number + 1,
                            trim($line)
                        );
                    }
                }
            }
        }

        sort($offenders);

        $this->assertSame([], $offenders, sprintf(
            "Comparación estricta de setting() contra una cadena numérica:\n  %s\n\n".
            "setting() convierte los numéricos con \$value + 0, así que devuelve int 1, no '1'. ".
            'Usa settingEnabled($clave, $porDefecto) para interruptores, o == si necesitas comparar el valor.',
            implode("\n  ", $offenders)
        ));
    }
}
