<?php

namespace Tests\Feature\Smoke;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Smoke\Concerns\SeedsPanelEntities;
use Tests\TestCase;

/**
 * Barrido de las rutas GET de panel: ninguna debe responder 5xx.
 *
 * Cada {slack}/{id} se resuelve con una entidad real deducida del prefijo de la
 * URI (siembra compartida con PanelWriteRoutesSmokeTest vía SeedsPanelEntities).
 * Cubre ~220 rutas de manager/support/mailers, incluidas las de detalle y
 * edición que ningún test tocaba.
 *
 * Nació como sonda de auditoría y encontró de golpe: cinco rutas apuntando a
 * métodos de controller inexistentes (500 garantizado), y cinco pantallas que
 * hacían `$user->relations->slack` sin comprobar el null. Se queda como red
 * permanente porque es el único test que recorre el panel de punta a punta.
 *
 * Si una ruta nueva no se puede resolver, aparece en el listado de saltadas del
 * mensaje de fallo en vez de pasar en silencio.
 */
class PanelRoutesSmokeTest extends TestCase
{
    use RefreshDatabase, SeedsPanelEntities;

    public function test_no_panel_route_returns_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seedEntities();

        $manager = User::factory()->manager()->create();
        $support = User::factory()->create(['role' => 'support']);
        $support->syncRoles(['support']);

        $bad = [];
        $skippedList = [];
        $ok = 0;
        $skipped = 0;

        foreach (app('router')->getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if (! str_contains($uri, '{')) {
                continue;
            }
            $name = (string) ($route->getName() ?? '');
            $panel = explode('.', $name)[0];
            if (! in_array($panel, ['manager', 'support', 'mailers'], true)) {
                continue;
            }

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
                $resp = $this->actingAs($user)->get('/'.ltrim($url, '/'));
                $code = $resp->getStatusCode();
                if ($code >= 500) {
                    $ex = $resp->baseResponse->exception ?? null;
                    $bad[] = sprintf(
                        "%d %-44s /%s\n      %s",
                        $code, $name, $url,
                        $ex ? get_class($ex).': '.substr($ex->getMessage(), 0, 120) : ''
                    );
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $bad[] = sprintf("EXC %-44s /%s\n      %s: %s", $name, $url, get_class($e), substr($e->getMessage(), 0, 120));
            }
        }

        $this->assertSame(
            [],
            $bad,
            sprintf(
                "%d ruta(s) de panel responden 5xx:\n\n%s\n\n(%d rutas OK, %d saltadas por no poder resolver sus parámetros:\n%s)",
                count($bad),
                implode("\n", $bad),
                $ok,
                $skipped,
                implode("\n", $skippedList)
            )
        );

        // Guardarraíl del propio test: si la siembra se rompe, la cobertura cae
        // en silencio y el barrido pasaría sin comprobar casi nada. Umbral
        // bajado de 200 a 180 tras la auditoría de código muerto que eliminó
        // ~54 rutas huérfanas de manager.*/mailers.* (sep-2026): el total real
        // de rutas con {param} bajó a ~198, cifra esperada, no una regresión.
        $this->assertGreaterThan(180, $ok, 'El barrido cubrió menos rutas de las esperadas: revisa la siembra.');
    }
}
