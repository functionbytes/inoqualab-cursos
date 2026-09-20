<?php

namespace Tests\Feature\Customers;

use App\Models\Order\Order;
use App\Models\User;
use Database\Seeders\CatalogsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el patrón AJAX de Mis pedidos: filtro por estado (condition_id),
 * búsqueda por número de orden, paginación, y que los contadores de las
 * pestañas no se vean afectados por el filtro/búsqueda activos.
 */
class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogsSeeder::class);
        $this->customer = User::factory()->customer()->create();
    }

    public function test_index_returns_the_full_view_for_a_normal_request(): void
    {
        Order::factory()->for($this->customer, 'user')->create();

        $this->actingAs($this->customer)
            ->get(route('customers.orders'))
            ->assertOk()
            ->assertViewIs('customers.views.orders.index');
    }

    public function test_index_returns_json_with_rendered_html_for_an_ajax_request(): void
    {
        Order::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html', 'total', 'label']);

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('pedido', $response->json('label'));
    }

    public function test_index_only_shows_the_authenticated_customers_orders(): void
    {
        $other = User::factory()->customer()->create();
        $mine = Order::factory()->for($this->customer, 'user')->create();
        Order::factory()->for($other, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($mine->slack, $response->json('html'));
    }

    public function test_index_filters_by_condition(): void
    {
        $pending = Order::factory()->for($this->customer, 'user')->pendiente()->create();
        Order::factory()->for($this->customer, 'user')->pagada()->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders', ['condition' => $pending->condition_id]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString($pending->slack, $response->json('html'));
    }

    public function test_index_search_filters_by_slack(): void
    {
        $match = Order::factory()->for($this->customer, 'user')->create();
        Order::factory()->for($this->customer, 'user')->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders', ['search' => $match->slack]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
    }

    public function test_index_condition_counts_are_not_affected_by_the_active_filter(): void
    {
        $pending = Order::factory()->for($this->customer, 'user')->pendiente()->create();
        Order::factory()->for($this->customer, 'user')->pagada()->count(2)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders', ['condition' => $pending->condition_id]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        // La página filtrada solo trae el pendiente...
        $this->assertSame(1, $response->json('total'));
        // ...pero el conteo total del usuario (3 pedidos) sigue disponible para las otras pestañas.
        $this->assertStringContainsString('Todos<span class="cnt">3</span>', $response->json('html'));
    }

    public function test_index_paginates_results(): void
    {
        Order::factory()->for($this->customer, 'user')->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.orders', ['page' => 2]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertStringContainsString('Mostrando 16-16 de 16 resultados', $response->json('html'));
    }
}
