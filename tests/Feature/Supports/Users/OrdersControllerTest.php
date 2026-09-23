<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Order\Order;
use App\Models\User;
use Database\Seeders\CatalogsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CatalogsSeeder::class);

        $this->support = User::factory()->support()->create();
        $this->customer = User::factory()->customer()->create();
    }

    public function test_index_lists_orders_for_the_user(): void
    {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->actingAs($this->support)
            ->get(route('support.users.orders.index', $this->customer->slack))
            ->assertOk()
            ->assertSee($order->reference);
    }

    public function test_index_filters_by_search(): void
    {
        $match = Order::factory()->create(['user_id' => $this->customer->id, 'reference' => 'FAC-MATCH']);
        $other = Order::factory()->create(['user_id' => $this->customer->id, 'reference' => 'FAC-OTHER']);

        $response = $this->actingAs($this->support)
            ->get(route('support.users.orders.index', [$this->customer->slack, 'search' => 'MATCH']));

        $response->assertOk()->assertSee($match->reference)->assertDontSee($other->reference);
    }

    public function test_index_search_with_no_matches_returns_empty_list(): void
    {
        Order::factory()->create(['user_id' => $this->customer->id, 'reference' => 'FAC-1']);

        $this->actingAs($this->support)
            ->get(route('support.users.orders.index', [$this->customer->slack, 'search' => 'noexiste123']))
            ->assertOk()
            ->assertSee('No hay ordenes');
    }
}
