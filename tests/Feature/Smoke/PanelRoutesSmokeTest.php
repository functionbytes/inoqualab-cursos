<?php

namespace Tests\Feature\Smoke;

use App\Models\Blog\Blog;
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
 * Barrido de las rutas GET de panel: ninguna debe responder 5xx.
 *
 * Cada {slack}/{id} se resuelve con una entidad real deducida del prefijo de la
 * URI. Cubre ~220 rutas de manager/support/mailers, incluidas las de detalle y
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
    use RefreshDatabase;

    /** palabra en la URI => valor con el que sustituir el parámetro */
    private array $bag = [];

    /** Tablas que no se pudieron sembrar (reduce cobertura, no rompe el test). */
    private array $seedFailures = [];

    private function seedEntities(): void
    {
        $course = Course::factory()->create();
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);
        $enterprise = Enterprise::factory()->create();
        $distributor = Distributor::factory()->create();
        $blog = Blog::factory()->create();
        $customer = User::factory()->create(['role' => 'customer']);
        $inscription = Inscription::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
        ]);

        $this->bag = [
            'users' => $customer->slack,
            'user' => $customer->slack,
            'staffs' => $customer->slack,
            'courses' => $course->slack,
            'course' => $course->slack,
            'lessons' => $course->slack,
            'chapters' => $chapter->slack ?? $chapter->id,
            'topics' => $chapter->slack ?? $chapter->id,
            'enterprises' => $enterprise->slack,
            'enterprise' => $enterprise->slack,
            'distributors' => $distributor->slack,
            'distributor' => $distributor->slack,
            'blogs' => $blog->slack,
            'blog' => $blog->slack,
            'inscriptions' => $inscription->slack,
            'inscription' => $inscription->slack,
        ];

        $this->seedSettings($course, $enterprise, $distributor, $customer);
    }

    /** Siembra las entidades de settings vía DB directo (evita fillable/mutators). */
    private function seedSettings($course, $enterprise, $distributor, $customer): void
    {
        $s = fn () => Str::random(10);
        $now = '2026-01-01 00:00:00';
        $failed = [];

        $rows = [
            'testimonies' => ['slack' => $s(), 'firstname' => 'Ana', 'lastname' => 'Ruiz', 'description' => '<p>Genial</p>', 'available' => 1],
            'documents' => ['slack' => $s(), 'title' => 'Doc', 'description' => '<p>x</p>', 'available' => 1],
            'contacts' => ['slack' => $s(), 'firstname' => 'Ana', 'lastname' => 'Ruiz', 'email' => 'a@b.com', 'cellphone' => '300', 'message' => 'hola', 'reviewed' => 0],
            'certifiers' => ['slack' => $s(), 'firstname' => 'Cert', 'lastname' => 'Uno', 'identification' => '1', 'available' => 1, 'profession' => 'Ing', 'description' => '<p>x</p>'],
            'trusteds' => ['slack' => $s(), 'title' => 'Aliado', 'slug' => 'aliado', 'available' => 1],
            'sliders' => ['slack' => $s(), 'title' => 'S', 'subtitle' => 'Sub', 'description' => 'D', 'available' => 1],
            'departments' => ['slack' => $s(), 'title' => 'Dep', 'slug' => 'dep', 'available' => 1],
            'certifications' => ['slack' => $s(), 'title' => 'Cert', 'slug' => 'cert', 'available' => 1],
            'faq_categories' => ['slack' => $s(), 'title' => 'Cat'],
            'instruction_categories' => ['slack' => $s(), 'title' => 'Cat'],
            'bundles' => ['slack' => $s(), 'title' => 'Paquete'],
            'coupons' => ['slack' => $s(), 'title' => 'C', 'code' => 'C10', 'amount' => 10, 'available' => 1],
            'seo_redirects' => ['source_path' => '/a', 'target_path' => '/b'],
            'seo_static_urls' => ['url' => '/estatica'],
            'seo_templates' => ['name' => 'Plantilla'],
            'mail_templates' => ['key' => 'k', 'name' => 'n', 'subject' => 's', 'content' => '<p>c</p>'],
            'newsletter_campaigns' => ['uid' => $s(), 'name' => 'n', 'subject' => 's', 'content' => '<p>c</p>'],
            'newsletter_lists' => ['slack' => $s(), 'name' => 'Lista'],
            'mailer_templates' => ['uid' => $s(), 'key' => 'k', 'name' => 'n'],
            'mailer_endpoints' => ['name' => 'n', 'slug' => 'ep', 'api_token' => 'tok'],
            'incoming_mails' => ['slack' => substr($s(), 0, 6), 'message_id' => 'mid', 'from' => 'a@b.com'],
            'analytics_report_schedules' => ['name' => 'n', 'frequency' => 'daily', 'email' => 'a@b.com'],
        ];

        foreach ($rows as $table => $data) {
            try {
                $data += ['created_at' => $now, 'updated_at' => $now];
                $id = DB::table($table)->insertGetId($data);
                $key = rtrim($table, 's');
                $value = $data['slack'] ?? $data['uid'] ?? $id;
                $this->bag[$table] = $value;
                $this->bag[$key] = $value;
                $this->bag[str_replace('_', '-', $table)] = $value;
            } catch (\Throwable $e) {
                $failed[] = $table.': '.substr($e->getMessage(), 0, 70);
            }
        }

        // FAQ e Instruction necesitan su categoría
        try {
            $fc = DB::table('faq_categories')->value('id');
            $v = $s();
            DB::table('faqs')->insert(['slack' => $v, 'title' => 'P', 'slug' => 'p', 'description' => '<p>r</p>', 'available' => 1, 'category_id' => $fc, 'created_at' => $now, 'updated_at' => $now]);
            $this->bag['faqs'] = $this->bag['faq'] = $v;
        } catch (\Throwable $e) {
            $failed[] = 'faqs: '.substr($e->getMessage(), 0, 70);
        }
        try {
            $ic = DB::table('instruction_categories')->value('id');
            $v = $s();
            DB::table('instructions')->insert(['slack' => $v, 'title' => 'I', 'slug' => 'i', 'short' => '<p>s</p>', 'description' => '<p>d</p>', 'available' => 1, 'category_id' => $ic, 'created_at' => $now, 'updated_at' => $now]);
            $this->bag['instructions'] = $this->bag['instruction'] = $v;
        } catch (\Throwable $e) {
            $failed[] = 'instructions: '.substr($e->getMessage(), 0, 70);
        }

        // seo_metas cuelga de un modelo
        try {
            $id = DB::table('seo_metas')->insertGetId([
                'seoable_type' => Course::class, 'seoable_id' => $course->id,
                'locale' => 'es', 'title' => 'T', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->bag['metas'] = $this->bag['seoMeta'] = $this->bag['schema-org'] = $this->bag['history'] = $id;
        } catch (\Throwable $e) {
            $failed[] = 'seo_metas: '.substr($e->getMessage(), 0, 70);
        }

        // Órdenes y facturas: necesitan sus catálogos
        try {
            foreach ([['order_condition', 'Pagada'], ['order_method', 'Wompi'], ['order_type', 'Curso']] as [$t, $title]) {
                DB::table($t)->insertOrIgnore(['id' => 1, 'slack' => $s(), 'title' => $title, 'slug' => strtolower($title)]);
            }
            $v = $s();
            DB::table('orders')->insert([
                'slack' => $v, 'number' => '1', 'reference' => 'REF1', 'user_id' => $customer->id,
                'type_id' => 1, 'method_id' => 1, 'condition_id' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->bag['orders'] = $this->bag['order'] = $v;
        } catch (\Throwable $e) {
            $failed[] = 'orders: '.substr($e->getMessage(), 0, 70);
        }
        try {
            foreach ([['invoice_condition', 'Pagada'], ['invoice_method', 'Transferencia']] as [$t, $title]) {
                DB::table($t)->insertOrIgnore(['id' => 1, 'slack' => $s(), 'title' => $title, 'slug' => strtolower($title)]);
            }
            $v = $s();
            DB::table('invoices')->insert([
                'slack' => $v, 'number' => '1', 'distributor_id' => $distributor->id,
                'method_id' => 1, 'condition_id' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->bag['invoices'] = $this->bag['invoice'] = $v;
        } catch (\Throwable $e) {
            $failed[] = 'invoices: '.substr($e->getMessage(), 0, 70);
        }

        // roles de Spatie ya sembrados
        $this->bag['roles'] = DB::table('roles')->value('id');

        // alias de nombres de parámetro a su entidad
        $this->bag['campaign'] = $this->bag['newsletter_campaigns'] ?? null;
        $this->bag['list'] = $this->bag['newsletter_lists'] ?? null;
        $this->bag['uid'] = $this->bag['mailer_templates'] ?? null;
        $this->bag['variable'] = $this->bag['mailer_variables'] ?? null;
        $this->bag['endpoint'] = $this->bag['mailer_endpoints'] ?? null;
        $this->bag['schedule'] = DB::table('analytics_report_schedules')->value('id');
        $this->bag['seoRedirect'] = DB::table('seo_redirects')->value('id');
        $this->bag['seoStaticUrl'] = DB::table('seo_static_urls')->value('id');
        $this->bag['seoTemplate'] = DB::table('seo_templates')->value('id');
        $this->bag['mails'] = $this->bag['incoming_mails'] ?? null;
        $this->bag = array_filter($this->bag, fn ($v) => $v !== null);

        // No se lanza excepción: una entidad que no se pueda sembrar solo
        // reduce la cobertura, y el assert final sobre $ok lo detecta.
        $this->seedFailures = $failed;
    }

    /** Deduce con qué valor sustituir el parámetro, mirando la URI de izq. a der. */
    private function resolve(string $uri, string $param): ?string
    {
        $segments = explode('/', $uri);

        // El segmento-entidad es el último literal antes del parámetro.
        $idx = null;
        foreach ($segments as $i => $s) {
            if (str_contains($s, '{'.$param)) {
                $idx = $i;
                break;
            }
        }
        if ($idx === null) {
            return null;
        }

        for ($i = $idx - 1; $i >= 0; $i--) {
            $key = rtrim($segments[$i], 's').'s';   // normalizar plural
            if (isset($this->bag[$segments[$i]])) {
                return $this->bag[$segments[$i]];
            }
            if (isset($this->bag[$key])) {
                return $this->bag[$key];
            }
        }

        // Último recurso: el nombre del propio parámetro
        return $this->bag[$param] ?? null;
    }

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
        // en silencio y el barrido pasaría sin comprobar casi nada.
        $this->assertGreaterThan(200, $ok, 'El barrido cubrió menos rutas de las esperadas: revisa la siembra.');
    }
}
