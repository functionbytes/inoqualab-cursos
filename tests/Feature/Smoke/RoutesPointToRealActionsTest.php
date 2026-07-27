<?php

namespace Tests\Feature\Smoke;

use Tests\TestCase;

/**
 * Toda ruta debe apuntar a una clase y un método que existan.
 *
 * Suena obvio, pero el proyecto acumulaba **diez** rutas cuyo método nunca se
 * escribió o se borró después: cinco en el panel de soporte, dos en el de
 * distribuidor y tres públicas. Todas devolvían `BadMethodCallException` (500).
 *
 * Las peores eran `blogs.categories` y `blogs.tags`: estaban enlazadas desde la
 * ficha del post y el widget lateral, así que cualquier visitante que pulsara
 * una categoría o una etiqueta del blog se llevaba un error del servidor.
 *
 * Este test recorre el router entero en milisegundos y no necesita base de
 * datos, así que es la red más barata del proyecto.
 */
class RoutesPointToRealActionsTest extends TestCase
{
    public function test_every_route_points_to_an_existing_controller_action(): void
    {
        $broken = [];

        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getAction('uses');

            // Closures y rutas sin controller quedan fuera.
            if (! is_string($action) || ! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action);

            if (! class_exists($class)) {
                $broken[] = sprintf('%s → la clase %s no existe', $route->uri(), $class);

                continue;
            }

            if (! method_exists($class, $method)) {
                $broken[] = sprintf(
                    '%s (%s) → %s::%s() no existe',
                    $route->getName() ?: $route->uri(),
                    $route->uri(),
                    class_basename($class),
                    $method
                );
            }
        }

        $this->assertSame(
            [],
            $broken,
            "Hay rutas apuntando a acciones inexistentes (500 garantizado):\n  ".implode("\n  ", $broken)
        );
    }
}
