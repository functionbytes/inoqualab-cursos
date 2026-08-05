<?php

namespace Tests\Feature\Managers\Enterprises;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión de los 2 IDOR reales de la auditoría de Empresas
 * (.claude/audits/manager-panel/empresas.md hallazgos #1 y #2/#3) y del bug
 * funcional crítico de CoursesImport (hallazgo #6).
 */
class EnterprisesSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seedOrderLookups();
    }

    /** Catálogos que InscriptionService::enrollSimple() necesita (condition/type/method). */
    private function seedOrderLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot-services', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om-credit', 'title' => 'Crédito', 'slug' => 'credit']);
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            DB::table('order_condition')->insertOrIgnore([
                'id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug,
            ]);
        }
    }

    private function createEnterprise(): Enterprise
    {
        return Enterprise::create([
            'slack' => (string) Str::uuid(),
            'title' => 'Empresa '.Str::random(6),
            'available' => 1,
        ]);
    }

    private function attachMember(Enterprise $enterprise, string $identification): User
    {
        $user = User::factory()->customer()->create(['identification' => $identification]);

        EnterpriseUser::create([
            'user_id' => $user->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        return $user;
    }

    public function test_enterprise_cannot_update_rates_belonging_to_another_enterprise(): void
    {
        $manager = User::factory()->manager()->create();

        $enterpriseA = $this->createEnterprise();
        $enterpriseB = $this->createEnterprise();

        $course = Course::factory()->create();
        $rateB = new EnterpriseCourse(['course_id' => $course->id, 'enterprise_id' => $enterpriseB->id]);
        $rateB->price = 500;
        $rateB->save();

        // El manager opera en el contexto de la empresa A, pero envía el id de
        // una tarifa (enterprise_course) que pertenece a la empresa B.
        $this->actingAs($manager)
            ->post(route('manager.enterprises.rates.update'), [
                'slack' => $enterpriseA->slack,
                'courses' => [$rateB->id => 999999],
            ])
            ->assertOk();

        $this->assertDatabaseHas('enterprise_course', [
            'id' => $rateB->id,
            'price' => 500,
        ]);
    }

    public function test_enterprise_cannot_reassign_or_enroll_users_from_another_enterprise(): void
    {
        $manager = User::factory()->manager()->create();

        $enterpriseA = $this->createEnterprise();
        $enterpriseB = $this->createEnterprise();

        $oldCourse = Course::factory()->create();
        $newCourse = Course::factory()->create();

        $userB = $this->attachMember($enterpriseB, 'ID-'.Str::random(8));

        $inscription = Inscription::factory()->create([
            'user_id' => $userB->id,
            'course_id' => $oldCourse->id,
        ]);

        // El manager reasigna en el contexto de la empresa A, pero la
        // identificación pertenece a un usuario de la empresa B.
        $this->actingAs($manager)
            ->post(route('manager.enterprises.action.reasign'), [
                'enterprise' => $enterpriseA->id,
                'old' => $oldCourse->id,
                'course' => $newCourse->id,
                'users' => [$userB->identification],
            ]);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'course_id' => $oldCourse->id,
        ]);
    }

    public function test_courses_import_processes_valid_file_without_exception(): void
    {
        $manager = User::factory()->manager()->create();

        $enterprise = $this->createEnterprise();
        $course = Course::factory()->create();
        $member = $this->attachMember($enterprise, 'MEMBER-1');

        $file = UploadedFile::fake()->createWithContent(
            'courses.csv',
            "identification\nMEMBER-1\n"
        );

        $this->actingAs($manager)
            ->post(route('manager.enterprises.courses.importation'), [
                'enterprise' => $enterprise->slack,
                'course' => $course->slack,
                'file' => $file,
            ])
            ->assertRedirect(route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]));

        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $member->id,
            'course_id' => $course->id,
        ]);
    }

    // ── Bug: CourseController::progress() usaba Order::with(['progress', ─────
    // ── 'course.lessons']) -- relaciones inexistentes en Order, 500 siempre ──

    public function test_progress_page_renders_instead_of_500(): void
    {
        $manager = User::factory()->manager()->create();

        $enterprise = $this->createEnterprise();
        $course = Course::factory()->create();
        $member = $this->attachMember($enterprise, 'ID-'.Str::random(8));

        $order = Order::create([
            'slack' => 'ord-'.Str::random(8),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.Str::random(6),
            'user_id' => $member->id,
            'type_id' => OrderType::where('slug', 'services')->first()->id,
            'method_id' => OrderMethod::where('slug', 'credit')->first()->id,
            'condition_id' => 4,
            'total_before_discount' => 0,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 0,
        ]);

        $inscription = Inscription::factory()->create([
            'user_id' => $member->id,
            'course_id' => $course->id,
            'order_id' => $order->id,
        ]);

        // Antes: Order::with(['progress', 'user', 'course.lessons'])->slack(...)
        // -- ni "progress" ni "course" son relaciones de Order -- lanzaba
        // RelationNotFoundException siempre, sin importar el estado de los datos.
        $this->actingAs($manager)
            ->get(route('manager.enterprises.courses.progress', $inscription->slack))
            ->assertOk();
    }

    public function test_enterprise_course_view_links_to_the_inscription_slack_not_the_user_slack(): void
    {
        $manager = User::factory()->manager()->create();

        $enterprise = $this->createEnterprise();
        $course = Course::factory()->create();
        $member = $this->attachMember($enterprise, 'ID-'.Str::random(8));

        $order = Order::create([
            'slack' => 'ord-'.Str::random(8),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.Str::random(6),
            'user_id' => $member->id,
            'type_id' => OrderType::where('slug', 'services')->first()->id,
            'method_id' => OrderMethod::where('slug', 'credit')->first()->id,
            'condition_id' => 4,
            'total_before_discount' => 0,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 0,
        ]);

        $inscription = Inscription::factory()->create([
            'user_id' => $member->id,
            'course_id' => $course->id,
            'order_id' => $order->id,
        ]);

        // Antes: el listado generaba el link con $user->slack, que
        // CourseController::progress()/CertificatesController::user() no
        // podían resolver (esperan el slack de la inscripción).
        $this->actingAs($manager)
            ->get(route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]))
            ->assertOk()
            ->assertSee(route('manager.enterprises.courses.progress', $inscription->slack), false)
            ->assertDontSee(route('manager.enterprises.courses.progress', $member->slack), false);
    }

    public function test_import_rejects_invalid_file_type(): void
    {
        $manager = User::factory()->manager()->create();

        $enterprise = $this->createEnterprise();
        $course = Course::factory()->create();

        $file = UploadedFile::fake()->create('malware.exe', 10);

        $this->actingAs($manager)
            ->postJson(route('manager.enterprises.courses.importation'), [
                'enterprise' => $enterprise->slack,
                'course' => $course->slack,
                'file' => $file,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }
}
