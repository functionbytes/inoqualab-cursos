<?php

namespace Tests\Feature\Smoke;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lanza cada ruta de escritura de panel SIN payload: ninguna debe dar 5xx.
 *
 * Una ruta bien construida responde 422 (validación), 302 (redirect con
 * errores) o 403/404. Un 500 significa que el controller lee $request->algo
 * sin validarlo antes y se lo pasa directo a la base de datos.
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
    use RefreshDatabase;

    public function test_no_write_route_returns_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $manager = User::factory()->manager()->create();
        $support = User::factory()->create(['role' => 'support']);
        $support->syncRoles(['support']);

        // Entidades por si alguna ruta resuelve un parámetro de URL
        $course = Course::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $distributor = Distributor::factory()->create();
        $customer = User::factory()->create(['role' => 'customer']);

        $bag = [
            'courses' => $course->slack, 'course' => $course->slack,
            'enterprises' => $enterprise->slack, 'enterprise' => $enterprise->slack,
            'distributors' => $distributor->slack, 'distributor' => $distributor->slack,
            'users' => $customer->slack, 'user' => $customer->slack,
            'staffs' => $customer->slack,
        ];

        $bad = [];
        $ok = 0;
        $skipped = 0;

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
                $segments = explode('/', $uri);
                $value = null;
                foreach ($segments as $i => $s) {
                    if (str_contains($s, '{'.$param)) {
                        for ($j = $i - 1; $j >= 0; $j--) {
                            if (isset($bag[$segments[$j]])) {
                                $value = $bag[$segments[$j]];
                                break 2;
                            }
                        }
                    }
                }
                $value ??= $bag[$param] ?? null;
                if ($value === null) {
                    $unresolved = true;
                    break;
                }
                $url = preg_replace('/\{'.$param.'\??\}/', (string) $value, $url, 1);
            }
            if ($unresolved) {
                $skipped++;

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
                "%d ruta(s) de escritura responden 5xx al recibir un payload vacío:\n\n%s\n\n(%d rutas OK, %d saltadas por no poder resolver sus parámetros)",
                count($bad),
                implode("\n", $bad),
                $ok,
                $skipped
            )
        );

        // Si la resolución de parámetros se rompe, la cobertura caería en
        // silencio y el barrido pasaría sin comprobar nada.
        $this->assertGreaterThan(240, $ok, 'El barrido cubrió menos rutas de las esperadas.');
    }
}
