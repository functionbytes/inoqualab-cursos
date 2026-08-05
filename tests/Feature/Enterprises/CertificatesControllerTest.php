<?php

namespace Tests\Feature\Enterprises;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * CertificatesController::user() leía $inscription->certificate sin
 * comprobar null: para un curso aún no completado (sin examen aprobado, sin
 * certificado emitido) revienta con 500 al construir el nombre del PDF.
 * Mismo bug replicado en Supports y Distributors (ver commit de la tanda).
 */
class CertificatesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_downloading_certificate_for_incomplete_course_returns_404_instead_of_500(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $enterprise = Enterprise::factory()->create();
        $staff = User::factory()->create(['role' => 'enterprise']);
        DB::table('enterprise_staff')->insert([
            'enterprise_id' => $enterprise->id,
            'user_id' => $staff->id,
        ]);

        $student = User::factory()->create(['role' => 'customer']);
        DB::table('enterprise_user')->insert([
            'enterprise_id' => $enterprise->id,
            'user_id' => $student->id,
        ]);

        $course = Course::factory()->create();
        $inscription = Inscription::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Sin certificado emitido (curso incompleto): antes de este fix,
        // $certificate->id sobre null reventaba al armar el nombre del PDF.
        $this->actingAs($staff)
            ->get(route('enterprise.users.certificate.user', $inscription->slack))
            ->assertNotFound();
    }
}
