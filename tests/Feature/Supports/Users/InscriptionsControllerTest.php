<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Inscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscriptionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    public function test_index_lists_customer_inscriptions(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create();

        // La vista pasa el titulo por Str::upper(): comparar en mayusculas
        // evita el falso negativo de assertSee() (case-sensitive) contra el
        // titulo Faker en minusculas/mixed-case.
        $this->actingAs($this->support)
            ->get(route('support.users.inscriptions', $customer->slack))
            ->assertOk()
            ->assertSee(mb_strtoupper($inscription->course->title));
    }

    public function test_index_refuses_a_manager_target(): void
    {
        // index() no verificaba el rol del usuario objetivo: un soporte podia
        // ver las inscripciones de cualquier manager/support solo con su slack.
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($this->support)
            ->get(route('support.users.inscriptions', $manager->slack))
            ->assertForbidden();
    }

    public function test_edit_refuses_a_manager_inscription(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $inscription = Inscription::factory()->for($manager)->create();

        $this->actingAs($this->support)
            ->get(route('support.users.inscriptions.edit', $inscription->slack))
            ->assertForbidden();
    }
}
