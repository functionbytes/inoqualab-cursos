<?php

namespace Tests\Feature\Managers\Mailer;

use App\Jobs\Mailer\SendEndpointEmailJob;
use App\Models\Mailer\MailerEndpoint;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Formulario de crear/editar endpoint: variables esperadas, obligatorias y
 * mapeo JSON -> plantilla (partial _variables.blade.php + variables.js).
 */
class MailerEndpointFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function manager(): User
    {
        return User::factory()->manager()->create();
    }

    private function endpoint(array $attributes = []): MailerEndpoint
    {
        return MailerEndpoint::create(array_merge([
            'name' => 'Pedido confirmado',
            'slug' => 'order-confirmed',
            'source' => 'api',
            'type' => 'transactional',
            'is_active' => true,
        ], $attributes));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pedido confirmado',
            'source' => 'api',
            'type' => 'transactional',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_edit_page_renders_the_variable_editors(): void
    {
        $endpoint = $this->endpoint([
            'expected_variables' => ['customer_email'],
            'required_variables' => ['customer_email'],
            'variable_mappings' => ['customer.email' => 'USER_EMAIL'],
        ]);

        $this->actingAs($this->manager())
            ->get(route('mailers.endpoints.edit', $endpoint))
            ->assertOk()
            ->assertSee('id="expectedVariablesContainer"', false)
            ->assertSee('value="customer.email"', false)
            ->assertSee('data-selected="USER_EMAIL"', false);
    }

    public function test_update_saves_cleaned_variables_and_mappings(): void
    {
        $endpoint = $this->endpoint();

        $this->actingAs($this->manager())
            ->patch(route('mailers.endpoints.update', $endpoint), $this->payload([
                'expected_variables' => [' customer_email ', '', 'customer_email', 'customer_name'],
                'required_variables' => ['customer_email', 'removed_variable'],
                'variable_mappings' => ['customer.email' => 'USER_EMAIL'],
            ]))
            ->assertRedirect(route('mailers.endpoints.edit', $endpoint));

        $endpoint->refresh();
        $this->assertSame(['customer_email', 'customer_name'], $endpoint->expected_variables);
        $this->assertSame(['customer_email'], $endpoint->required_variables);
        $this->assertSame(['customer.email' => 'USER_EMAIL'], $endpoint->variable_mappings);
    }

    public function test_update_clears_variables_when_every_row_is_removed(): void
    {
        $endpoint = $this->endpoint([
            'expected_variables' => ['customer_email'],
            'required_variables' => ['customer_email'],
            'variable_mappings' => ['customer_email' => 'USER_EMAIL'],
        ]);

        $this->actingAs($this->manager())
            ->patch(route('mailers.endpoints.update', $endpoint), $this->payload())
            ->assertRedirect();

        $endpoint->refresh();
        $this->assertSame([], $endpoint->expected_variables);
        $this->assertSame([], $endpoint->required_variables);
        $this->assertSame([], $endpoint->variable_mappings);
    }

    public function test_saved_mapping_direction_matches_the_send_job(): void
    {
        $endpoint = $this->endpoint();

        $this->actingAs($this->manager())
            ->patch(route('mailers.endpoints.update', $endpoint), $this->payload([
                'variable_mappings' => ['customer.email' => 'USER_EMAIL'],
            ]));

        $job = (new \ReflectionClass(SendEndpointEmailJob::class))->newInstanceWithoutConstructor();
        $mapVariables = new \ReflectionMethod($job, 'mapVariables');

        $variables = $mapVariables->invoke($job, ['customer' => ['email' => 'ana@example.com']], $endpoint->refresh());

        $this->assertSame(['USER_EMAIL' => 'ana@example.com'], $variables);
    }
}
