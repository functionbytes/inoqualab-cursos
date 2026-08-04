<?php

namespace Tests\Feature\Smoke;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Smoke\Concerns\SeedsPanelEntities;
use Tests\TestCase;

/**
 * Lanza cada ruta de escritura de panel SIN payload: ninguna debe dar 5xx.
 *
 * Una ruta bien construida responde 422 (validación), 302 (redirect con
 * errores) o 403/404. Un 500 significa que el controller lee $request->algo
 * sin validarlo antes y se lo pasa directo a la base de datos.
 *
 * Comparte la siembra con PanelRoutesSmokeTest vía SeedsPanelEntities: antes
 * tenía su propio bag con solo 4 entidades y saltaba ~106 rutas de escritura
 * por no poder resolver sus parámetros (destroy/update de orders, invoices,
 * documents, faqs, instructions...) — el mismo tipo de hueco que en
 * PortalRoutesSmokeTest ya dio bugs reales al cerrarlo.
 *
 * Encontró de una tacada: nueve `store()` sin Form Request que insertaban NULL
 * en columnas NOT NULL, un Form Request type-hinted sin importar (la
 * importación masiva de usuarios estaba rota entera), un generate_slack sobre
 * una tabla mal escrita, y dos rutas apuntando a métodos inexistentes.
 *
 * Complementa PanelRoutesSmokeTest, que cubre el lado de lectura.
 *
 * ⚠️ Este barrido tiene efectos fuera de la base de datos. Las acciones de
 * `manager.seo.robots.*` y `manager.settings.seo` hacen
 * `File::put(public_path('robots.txt'), ...)`, así que ejecutarlo REESCRIBE ese
 * archivo con el contenido por defecto. RefreshDatabase revierte la BD, no el
 * disco. En CI da igual (contenedor efímero); en local conviene saberlo.
 *
 * Y ojo: si `public/robots.txt` llega a existir, el servidor web sirve el
 * archivo estático y la ruta dinámica `/robots.txt` deja de aplicarse — dos
 * fuentes de verdad para lo mismo.
 */
class PanelWriteRoutesSmokeTest extends TestCase
{
    use RefreshDatabase, SeedsPanelEntities;

    public function test_no_write_route_returns_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seedEntities();

        $manager = User::factory()->manager()->create();
        $support = User::factory()->create(['role' => 'support']);
        $support->syncRoles(['support']);

        $bad = [];
        $ok = 0;
        $skipped = 0;
        $skippedList = [];

        foreach (app('router')->getRoutes() as $route) {
            $methods = array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']);
            if (! $methods) {
                continue;
            }
            $verb = strtolower(reset($methods));

            $name = (string) ($route->getName() ?? '');
            $panel = explode('.', $name)[0];
            if (! in_array($panel, ['manager', 'support', 'mailers'], true)) {
                continue;
            }

            // Resolver parámetros de URL si los hay
            $uri = $route->uri();
            preg_match_all('/\{(\w+)\??\}/', $uri, $m);
            $url = $uri;
            $unresolved = false;
            foreach ($m[1] as $param) {
                $value = $this->resolve($uri, $param);
                if ($value === null) {
                    $unresolved = true;
                    break;
                }
                $url = preg_replace('/\{'.$param.'\??\}/', (string) $value, $url, 1);
            }
            if ($unresolved) {
                $skipped++;
                $skippedList[] = sprintf('%-46s /%s', $name, $uri);

                continue;
            }

            $user = $panel === 'support' ? $support : $manager;

            try {
                $resp = $this->actingAs($user)->json(strtoupper($verb), '/'.ltrim($url, '/'), []);
                $code = $resp->getStatusCode();
                if ($code >= 500) {
                    $ex = $resp->baseResponse->exception ?? null;
                    $bad[] = sprintf(
                        "%s %-46s /%s\n      %s",
                        strtoupper($verb), $name, $url,
                        $ex ? get_class($ex).': '.substr($ex->getMessage(), 0, 115) : ''
                    );
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $bad[] = sprintf("EXC %-46s /%s\n      %s: %s", $name, $url, get_class($e), substr($e->getMessage(), 0, 115));
            }
        }

        $this->assertSame(
            [],
            $bad,
            sprintf(
                "%d ruta(s) de escritura responden 5xx al recibir un payload vacío:\n\n%s\n\n(%d rutas OK, %d saltadas por no poder resolver sus parámetros:\n%s)",
                count($bad),
                implode("\n", $bad),
                $ok,
                $skipped,
                implode("\n", $skippedList)
            )
        );

        // Si la resolución de parámetros se rompe, la cobertura caería en
        // silencio y el barrido pasaría sin comprobar nada.
        $this->assertGreaterThan(240, $ok, 'El barrido cubrió menos rutas de las esperadas.');
    }
}
