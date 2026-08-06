<?php

namespace Tests\Feature\Pages;

use App\Models\Citie;
use App\Models\Countrie;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: getCities() (autocompletado público de ciudades) consultaba
 * sin limit(): un término de un carácter devolvía TODAS las ciudades que
 * empiezan por esa letra, sin tope, servido a peticiones anónimas repetidas
 * sin throttle en la ruta.
 */
class UtilitiesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_cities_caps_the_number_of_results(): void
    {
        $countrie = Countrie::create(['title' => 'Colombia']);
        $state = State::create(['title' => 'Antioquia', 'countrie_id' => $countrie->id]);

        for ($i = 1; $i <= 25; $i++) {
            Citie::create(['title' => "Aldea {$i}", 'state_id' => $state->id]);
        }

        $response = $this->getJson(route('cities', ['term' => 'Aldea']))->assertOk();

        $this->assertLessThanOrEqual(20, count($response->json()));
    }

    public function test_get_cities_returns_empty_array_without_a_term(): void
    {
        $this->getJson(route('cities'))
            ->assertOk()
            ->assertExactJson([]);
    }
}
