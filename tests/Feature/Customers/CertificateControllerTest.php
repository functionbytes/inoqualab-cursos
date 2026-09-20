<?php

namespace Tests\Feature\Customers;

use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el patrón AJAX de Mis certificados: filtro por estado (vigente/por
 * vencer/vencido), búsqueda, paginación y que los contadores de las pestañas
 * reflejen TODO el conjunto del usuario, no solo la página cargada.
 */
class CertificateControllerTest extends TestCase
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
        Certificate::factory()->for($this->customer, 'user')->create();

        $this->actingAs($this->customer)
            ->get(route('customers.certificates'))
            ->assertOk()
            ->assertViewIs('customers.views.certificates.index');
    }

    public function test_index_returns_json_with_rendered_html_for_an_ajax_request(): void
    {
        Certificate::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html', 'total', 'label']);

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('certificado', $response->json('label'));
    }

    public function test_index_only_shows_the_authenticated_customers_certificates(): void
    {
        $other = User::factory()->customer()->create();
        $mine = Certificate::factory()->for($this->customer, 'user')->create();
        Certificate::factory()->for($other, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($mine->slack, $response->json('html'));
    }

    public function test_index_filters_by_expired_state(): void
    {
        $expired = Certificate::factory()->for($this->customer, 'user')->expired()->create();
        Certificate::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['state' => 'expired']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($expired->slack, $response->json('html'));
    }

    public function test_index_filters_by_valid_state(): void
    {
        Certificate::factory()->for($this->customer, 'user')->expired()->create();
        $valid = Certificate::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['state' => 'valid']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($valid->slack, $response->json('html'));
    }

    public function test_index_filters_by_soon_state(): void
    {
        $soon = Certificate::factory()->for($this->customer, 'user')->expiringSoon()->create();
        Certificate::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['state' => 'soon']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($soon->slack, $response->json('html'));
    }

    public function test_index_search_filters_by_course_title(): void
    {
        $match = Certificate::factory()->for($this->customer, 'user')->create();
        $match->course->update(['title' => 'Manipulación de alimentos']);
        Certificate::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['search' => 'manipulación']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
    }

    public function test_index_state_counts_reflect_the_full_set_not_just_the_current_page(): void
    {
        Certificate::factory()->for($this->customer, 'user')->expired()->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['state' => 'expired']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        // paginationNumber() = 15: la página trae 15, pero el total real es 16.
        $this->assertSame(16, $response->json('total'));
        $this->assertStringContainsString('Vencidos<span class="cnt">16</span>', $response->json('html'));
    }

    public function test_index_paginates_results(): void
    {
        Certificate::factory()->for($this->customer, 'user')->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.certificates', ['page' => 2]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertStringContainsString('Mostrando 16-16 de 16 resultados', $response->json('html'));
    }
}
