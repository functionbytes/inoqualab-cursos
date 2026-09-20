<?php

namespace Tests\Feature\Customers;

use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el patrón AJAX de Mis cursos: filtro por estado derivado de
 * percent/expire (en progreso/pendiente/completado/vencido), búsqueda por
 * título del curso, paginación y que los contadores reflejen TODO el
 * conjunto del usuario, no solo la página cargada.
 */
class CoursesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->customer()->create();
    }

    public function test_index_returns_the_full_view_for_a_normal_request(): void
    {
        Inscription::factory()->for($this->customer, 'user')->create();

        $this->actingAs($this->customer)
            ->get(route('customers.courses'))
            ->assertOk()
            ->assertViewIs('customers.views.courses.index');
    }

    public function test_index_returns_json_with_rendered_html_for_an_ajax_request(): void
    {
        Inscription::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html', 'total', 'label']);

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('curso', $response->json('label'));
    }

    public function test_index_only_shows_the_authenticated_customers_inscriptions(): void
    {
        $other = User::factory()->customer()->create();
        $mine = Inscription::factory()->for($this->customer, 'user')->create();
        Inscription::factory()->for($other, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($mine->slack, $response->json('html'));
    }

    public function test_index_filters_by_done_state(): void
    {
        $done = Inscription::factory()->for($this->customer, 'user')->culminated()->create();
        Inscription::factory()->for($this->customer, 'user')->create(['percent' => 0]);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['state' => 'done']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($done->slack, $response->json('html'));
    }

    public function test_index_filters_by_expired_state(): void
    {
        // La tarjeta de un curso vencido no expone $inscription->slack en el
        // HTML (su botón "Renovar acceso" usa el slack del CURSO, no el de la
        // inscripción) -- se identifica por el título del curso en su lugar.
        $expired = Inscription::factory()->for($this->customer, 'user')->expired()->create();
        $expired->course->update(['title' => 'Curso con acceso vencido']);
        Inscription::factory()->for($this->customer, 'user')->create(['percent' => 0]);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['state' => 'expired']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString('Curso con acceso vencido', $response->json('html'));
    }

    public function test_index_filters_by_pending_state(): void
    {
        $pending = Inscription::factory()->for($this->customer, 'user')->create(['percent' => 0]);
        Inscription::factory()->for($this->customer, 'user')->create(['percent' => 40]);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['state' => 'pending']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($pending->slack, $response->json('html'));
    }

    public function test_index_filters_by_progress_state(): void
    {
        $inProgress = Inscription::factory()->for($this->customer, 'user')->create(['percent' => 40]);
        Inscription::factory()->for($this->customer, 'user')->create(['percent' => 0]);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['state' => 'progress']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($inProgress->slack, $response->json('html'));
    }

    public function test_index_search_filters_by_course_title(): void
    {
        $match = Inscription::factory()->for($this->customer, 'user')->create();
        $match->course->update(['title' => 'Manipulación de alimentos']);
        Inscription::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['search' => 'manipulación']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
    }

    public function test_index_state_counts_reflect_the_full_set_not_just_the_current_page(): void
    {
        Inscription::factory()->for($this->customer, 'user')->culminated()->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['state' => 'done']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(16, $response->json('total'));
        $this->assertStringContainsString('Completados<span class="cnt">16</span>', $response->json('html'));
    }

    public function test_index_paginates_results(): void
    {
        Inscription::factory()->for($this->customer, 'user')->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.courses', ['page' => 2]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertStringContainsString('Mostrando 16-16 de 16 resultados', $response->json('html'));
    }
}
