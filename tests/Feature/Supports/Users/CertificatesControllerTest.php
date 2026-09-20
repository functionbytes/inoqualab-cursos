<?php

namespace Tests\Feature\Supports\Users;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificatesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    public function test_index_lists_a_customers_certificates(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->get(route('support.users.courses.certificates', $customer->slack))
            ->assertOk();
    }

    public function test_index_refuses_a_manager_target(): void
    {
        // index() no verificaba el rol del usuario objetivo: un soporte podia
        // ver los certificados de cualquier manager/support solo con su slack.
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($this->support)
            ->get(route('support.users.courses.certificates', $manager->slack))
            ->assertForbidden();
    }

    public function test_broad_refuses_a_manager_target(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($this->support)
            ->get(route('support.enterprises.users.certificate.broad', $manager->slack))
            ->assertForbidden();
    }
}
