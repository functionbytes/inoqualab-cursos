<?php

namespace Tests\Feature\Pages;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regresión: a diferencia de sus rutas hermanas (response/sandbox/simulate,
 * todas con throttle), payments.wompi.webhook no tenía ningún rate limit.
 * La firma inválida se rechaza rápido, pero cada intento igual computa un
 * SHA256 y consulta logs -- sin límite es una vía de DoS barata contra un
 * endpoint público sin CSRF.
 */
class WompiWebhookThrottleTest extends TestCase
{
    public function test_webhook_route_has_a_throttle_middleware(): void
    {
        $route = Route::getRoutes()->getByName('payments.wompi.webhook');

        $this->assertNotNull($route, 'La ruta payments.wompi.webhook ya no existe.');

        $hasThrottle = collect($route->gatherMiddleware())
            ->contains(fn ($middleware) => str_starts_with($middleware, 'throttle:'));

        $this->assertTrue($hasThrottle, 'payments.wompi.webhook debe tener middleware throttle, igual que sus rutas hermanas.');
    }
}
