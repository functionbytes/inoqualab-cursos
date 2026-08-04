<?php

namespace Tests\Feature\Smoke;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Barrido de los cinco portales que no cubren los otros smoke tests:
 * customers, distributor, enterprise, accounting y support. Ninguna ruta
 * debe dar 5xx.
 *
 * A diferencia del panel de manager, estos portales exigen que el usuario tenga
 * una entidad asociada (distribuidor, empresa) y la resuelven vía
 * `app('distributor')` / `app('enterprise')`, así que la siembra tiene que
 * montar esas relaciones o el barrido solo vería 403. `support` es la
 * excepción: IsSupport solo exige `role === 'support'`, sin entidad propia.
 */
class PortalRoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $bag = [];

    private array $users = [];

    private function seedWorld(): void
    {
        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $course = Course::factory()->create();

        DB::table('distributor_enterprises')->insertOrIgnore([
            'distributor_id' => $distributor->id,
            'enterprise_id' => $enterprise->id,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        DB::table('enterprise_user')->insertOrIgnore([
            'enterprise_id' => $enterprise->id,
            'user_id' => $customer->id,
        ]);

        $inscription = Inscription::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
        ]);

        // Un usuario por portal, con su entidad enlazada.
        $this->users = [
            'customers' => $customer,
            'distributor' => User::factory()->create(['role' => 'distributor']),
            'enterprise' => User::factory()->create(['role' => 'enterprise']),
            'accounting' => User::factory()->create(['role' => 'accounting']),
            'support' => User::factory()->create(['role' => 'support']),
        ];
        foreach (['distributor' => 'distributor', 'enterprise' => 'enterprise', 'accounting' => 'accounting', 'support' => 'support'] as $k => $role) {
            try {
                $this->users[$k]->assignRole($role);
            } catch (\Throwable) {
            }
        }
        $this->users['customers']->assignRole('customer');

        // Relaciones que los middlewares IsDistributor / IsEnterprise resuelven
        DB::table('distributor_staff')->insertOrIgnore([
            'distributor_id' => $distributor->id,
            'user_id' => $this->users['distributor']->id,
        ]);
        DB::table('enterprise_staff')->insertOrIgnore([
            'enterprise_id' => $enterprise->id,
            'user_id' => $this->users['enterprise']->id,
        ]);

        DB::table('course_types')->insertOrIgnore([
            ['id' => 7, 'title' => 'TEXTO', 'slug' => 'texto'],
        ]);
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);
        $lesson = DB::table('course_lessons')->insertGetId([
            'slack' => Str::random(10), 'title' => 'LECCION', 'available' => 1,
            'position' => 1, 'type_id' => 7, 'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->bag = [
            'courses' => $course->slack, 'course' => $course->slack,
            'enterprises' => $enterprise->slack, 'enterprise' => $enterprise->slack,
            'distributors' => $distributor->slack, 'distributor' => $distributor->slack,
            'users' => $customer->slack, 'user' => $customer->slack,
            'staffs' => $customer->slack,
            'inscription' => $inscription->slack, 'inscriptions' => $inscription->slack,
            'content' => $inscription->slack,
            'lesson' => $lesson, 'lesion' => $lesson,
        ];
    }

    private function resolve(string $uri, string $param): ?string
    {
        $segments = explode('/', $uri);
        foreach ($segments as $i => $s) {
            if (! str_contains($s, '{'.$param)) {
                continue;
            }
            for ($j = $i - 1; $j >= 0; $j--) {
                if (isset($this->bag[$segments[$j]])) {
                    return (string) $this->bag[$segments[$j]];
                }
            }
        }

        return isset($this->bag[$param]) ? (string) $this->bag[$param] : null;
    }

    public function test_no_portal_route_returns_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seedWorld();

        $bad = [];
        $ok = 0;
        $skipped = [];

        foreach (app('router')->getRoutes() as $route) {
            $name = (string) ($route->getName() ?? '');
            $portal = explode('.', $name)[0];
            if (! isset($this->users[$portal])) {
                continue;
            }

            $methods = array_diff($route->methods(), ['HEAD', 'OPTIONS']);
            $verb = in_array('GET', $methods, true) ? 'GET' : strtoupper((string) reset($methods));

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
                $url = preg_replace('/\{'.$param.'\??\}/', $value, $url, 1);
            }
            if ($unresolved) {
                $skipped[] = sprintf('%-44s /%s', $name, $uri);

                continue;
            }

            try {
                $resp = $this->actingAs($this->users[$portal])->json($verb, '/'.ltrim($url, '/'), []);
                if ($resp->getStatusCode() >= 500) {
                    $ex = $resp->baseResponse->exception ?? null;
                    $bad[] = sprintf(
                        "%-5s %-44s /%s\n      %s",
                        $verb, $name, $url,
                        $ex ? get_class($ex).': '.substr($ex->getMessage(), 0, 115) : ''
                    );
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $bad[] = sprintf("EXC   %-44s /%s\n      %s: %s", $name, $url, get_class($e), substr($e->getMessage(), 0, 115));
            }
        }

        $this->assertSame([], $bad, sprintf(
            "%d ruta(s) de portal responden 5xx:\n\n%s\n\n(%d OK, %d saltadas:\n%s)",
            count($bad), implode("\n", $bad), $ok, count($skipped), implode("\n", $skipped)
        ));

        $this->assertGreaterThan(60, $ok, 'El barrido cubrió menos rutas de las esperadas.');
    }
}
