<?php

namespace Tests\Feature\Managers\Mailer;

use App\Jobs\Mailer\SendEndpointEmailJob;
use App\Models\Mailer\MailerEndpoint;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Cobertura del API pública transaccional de MailerEndpoint
 * (POST /api/email-endpoints/{slug}/send): autenticación por token,
 * validación de variables requeridas y encolado del envío.
 */
class MailerEndpointApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function endpoint(): MailerEndpoint
    {
        return MailerEndpoint::create([
            'name' => 'Pedido confirmado',
            'slug' => 'order-confirmed',
            'source' => 'api',
            'type' => 'transactional',
            'required_variables' => ['order_id'],
            'is_active' => true,
        ]);
    }

    public function test_send_rejects_missing_token(): void
    {
        $endpoint = $this->endpoint();

        $this->postJson("/api/email-endpoints/{$endpoint->slug}/send", [
            'email' => 'cliente@example.com', 'order_id' => 1,
        ])->assertStatus(401);
    }

    public function test_send_rejects_invalid_token(): void
    {
        $endpoint = $this->endpoint();

        $this->withHeaders(['X-API-Token' => 'token-incorrecto'])
            ->postJson("/api/email-endpoints/{$endpoint->slug}/send", [
                'email' => 'cliente@example.com', 'order_id' => 1,
            ])->assertStatus(401);
    }

    public function test_send_queues_email_with_valid_token(): void
    {
        Queue::fake();
        $endpoint = $this->endpoint();

        $this->withHeaders(['X-API-Token' => $endpoint->api_token])
            ->postJson("/api/email-endpoints/{$endpoint->slug}/send", [
                'email' => 'cliente@example.com', 'order_id' => 99,
            ])->assertStatus(202);

        Queue::assertPushed(SendEndpointEmailJob::class);
        $this->assertDatabaseHas('mailer_endpoint_logs', ['mailer_endpoint_id' => $endpoint->id]);
    }

    public function test_send_validates_required_variables(): void
    {
        Queue::fake();
        $endpoint = $this->endpoint();

        // Falta 'order_id' (required) → 422 y no se encola.
        $this->withHeaders(['X-API-Token' => $endpoint->api_token])
            ->postJson("/api/email-endpoints/{$endpoint->slug}/send", [
                'email' => 'cliente@example.com',
            ])->assertStatus(422);

        Queue::assertNotPushed(SendEndpointEmailJob::class);
    }

    public function test_send_returns_404_for_unknown_endpoint(): void
    {
        $this->withHeaders(['X-API-Token' => 'x'])
            ->postJson('/api/email-endpoints/no-existe/send', ['email' => 'a@b.com'])
            ->assertStatus(404);
    }

    public function test_info_is_public_and_returns_contract(): void
    {
        $endpoint = $this->endpoint();

        $this->getJson("/api/email-endpoints/{$endpoint->slug}/info")
            ->assertOk();
    }
}
