<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Guardarraíl: env() solo puede usarse dentro de config/.
 *
 * En cuanto se ejecuta `config:cache` (obligatorio en producción para no
 * releer 30 ficheros por petición), Laravel deja de cargar el .env y env()
 * devuelve null en todas partes. Un `env()` en app/ no revienta: devuelve
 * null y la funcionalidad se apaga en silencio.
 *
 * Este proyecto ya lo tenía en tres sitios escritos como
 * `config('services.x.y', env('X_Y', ''))`, que además engaña: parece que
 * config() es la fuente y env() el respaldo, cuando la clave ni siquiera
 * existía en config/services.php y el valor real venía siempre de env().
 * Con la config cacheada, DeepL y Google Search Console habrían dejado de
 * funcionar sin un solo error en el log.
 */
class NoEnvCallsOutsideConfigTest extends TestCase
{
    public function test_app_directory_does_not_call_env(): void
    {
        $offenders = [];

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            foreach (file($file->getPathname()) as $number => $line) {
                // \b evita cazar getenv(), Str::env() o $this->env().
                if (preg_match('/(?<![\w>$:])env\s*\(/', $line)) {
                    $offenders[] = sprintf(
                        '%s:%d  %s',
                        str_replace(base_path().'/', '', $file->getPathname()),
                        $number + 1,
                        trim($line)
                    );
                }
            }
        }

        sort($offenders);

        $this->assertSame([], $offenders, sprintf(
            "env() usado fuera de config/:\n  %s\n\n".
            'Con config:cache activo (producción) env() devuelve null. Declara la clave en '.
            'config/ y léela con config(). Cuidado al hacerlo: una vez la clave existe, el '.
            'segundo argumento de config() ya no actúa como valor por defecto.',
            implode("\n  ", $offenders)
        ));
    }
}
