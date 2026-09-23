<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Testimonie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: TestimoniesController::index() aplicaba el filtro `available`
 * fuera del grupo OR de la búsqueda por texto — `->where(...)->orWhere(...)
 * ->orWhere(...)` sin closure interno, así que `WHERE a OR b OR (c AND
 * available = ?)` solo filtraba por disponibilidad la última condición OR,
 * no las tres. Combinar búsqueda + filtro "No disponibles" devolvía
 * testimonios disponibles igual.
 */
class TestimoniesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_search_combined_with_available_filter_excludes_available_testimonies(): void
    {
        $manager = User::factory()->manager()->create();

        Testimonie::create([
            'slack' => (string) Str::uuid(),
            'firstname' => 'Ana',
            'lastname' => 'Available',
            'available' => 1,
        ]);
        Testimonie::create([
            'slack' => (string) Str::uuid(),
            'firstname' => 'Andres',
            'lastname' => 'Unavailable',
            'available' => 0,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.testimonies', ['search' => 'a', 'available' => 0]))
            ->assertOk();

        $testimonies = $response->viewData('testimonies');

        $this->assertTrue($testimonies->contains('lastname', 'Unavailable'), 'Debe incluir el testimonio no disponible que matchea la búsqueda.');
        $this->assertFalse($testimonies->contains('lastname', 'Available'), 'NO debe incluir testimonios disponibles cuando el filtro pide "no disponibles".');
    }
}
