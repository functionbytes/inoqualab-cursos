<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: PagesController::home() (route('home')) hacía
 * redirect()->route(User::auth()->redirect()) sin comprobar si había sesión.
 * route('home') se usa como "volver al inicio" desde varias vistas PÚBLICAS
 * (confirmación/baja de newsletter, cuenta deshabilitada, verificación de
 * email) donde el visitante no está autenticado -- User::auth() (Auth::user())
 * es null ahí, y ->redirect() sobre null era un error fatal.
 */
class HomeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visiting_home_is_redirected_to_the_public_index_without_500(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('index'));
    }

    public function test_authenticated_customer_visiting_home_is_redirected_to_their_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertRedirect(route('customers.dashboard'));
    }
}
