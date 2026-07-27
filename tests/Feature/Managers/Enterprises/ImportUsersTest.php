<?php

namespace Tests\Feature\Managers\Enterprises;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * `UserController::importation()` estaba type-hinted con `ImportUsersRequest`
 * pero el `use` no existía en el archivo, así que PHP lo resolvía contra el
 * namespace del propio controller y Laravel lanzaba
 * `ReflectionException: Class "...\Managers\Enterprises\ImportUsersRequest"
 * does not exist`.
 *
 * Resultado: la importación masiva de usuarios de empresa devolvía 500 en
 * cualquier intento, con Form Request y validación incluidos pero inalcanzables.
 */
class ImportUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->enterprise = Enterprise::factory()->create();
    }

    public function test_importation_endpoint_is_reachable_and_validates(): void
    {
        // Sin archivo: debe responder 422 de validación, no un 500 por la clase
        // de Form Request inexistente.
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.users.importation'), [
                'enterprise' => $this->enterprise->slack,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_importation_rejects_a_file_that_is_not_a_spreadsheet(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.users.importation'), [
                'enterprise' => $this->enterprise->slack,
                'file' => UploadedFile::fake()->create('usuarios.exe', 10),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_importation_requires_an_enterprise(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.users.importation'), [
                'file' => UploadedFile::fake()->create('usuarios.xlsx', 10),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('enterprise');
    }
}
