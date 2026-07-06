<?php

namespace Tests\Feature\Managers\Users;

use App\Models\User;
use App\Models\Users\Certificate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regresión del hallazgo #2 de la auditoría de Usuarios: CertificatesController
 * servía PDFs solo por slack, sin verificación propia de propiedad/permiso,
 * dependiendo 100% del permiso derivado por convención de la ruta. Ahora
 * `download()`/`user()`/`course()`/`broad()` llaman `$this->authorize()`
 * explícitamente contra CertificatePolicy/UserPolicy.
 */
class CertificatesOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function certificateFor(User $owner): Certificate
    {
        return Certificate::create([
            'slack' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'start_at' => now(),
            'end_at' => now()->addYear(),
        ]);
    }

    public function test_actor_without_certificates_permission_cannot_download_another_users_certificate(): void
    {
        $actor = User::factory()->manager()->create();
        $actor->syncRoles([]);
        $actor->syncPermissions(['users.view']); // sin certificates.view ni .manage
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::factory()->customer()->create();
        $certificate = $this->certificateFor($owner);

        $this->actingAs($actor)
            ->get(route('manager.certificate.download', $certificate->slack))
            ->assertForbidden();
    }

    public function test_actor_with_view_only_cannot_download_another_users_certificate(): void
    {
        // Tiene el permiso "view" pero no "manage": sin el manage no puede
        // bypasear el chequeo de propiedad, y el certificado no es suyo.
        $actor = User::factory()->manager()->create();
        $actor->syncRoles([]);
        $actor->syncPermissions(['certificates.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::factory()->customer()->create();
        $certificate = $this->certificateFor($owner);

        $this->actingAs($actor)
            ->get(route('manager.certificate.download', $certificate->slack))
            ->assertForbidden();
    }

    public function test_full_manager_can_view_any_users_certificate(): void
    {
        // certificates.manage (parte del set completo del manager) bypasea el
        // chequeo de propiedad: comportamiento de negocio esperado, no roto.
        $actor = User::factory()->manager()->create();
        $owner = User::factory()->customer()->create();
        $certificate = $this->certificateFor($owner);

        $this->assertTrue(
            Gate::forUser($actor)->allows('view', $certificate),
            'Un manager completo (certificates.manage) debe poder ver el certificado de cualquier usuario.'
        );
    }

    public function test_actor_without_users_permission_cannot_download_broad_certificate_of_another_user(): void
    {
        $actor = User::factory()->manager()->create();
        $actor->syncRoles([]);
        $actor->syncPermissions(['certificates.view']); // sin users.view
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::factory()->customer()->create();

        $this->actingAs($actor)
            ->get(route('manager.certificate.broad', $owner->slack))
            ->assertForbidden();
    }
}
