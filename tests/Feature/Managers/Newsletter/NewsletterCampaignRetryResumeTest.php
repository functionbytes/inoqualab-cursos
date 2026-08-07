<?php

namespace Tests\Feature\Managers\Newsletter;

use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: SendNewsletterCampaignJob no trackeaba qué suscriptor ya había
 * recibido el correo. Si el job fallaba a mitad de camino (timeout a los
 * 3600s, $tries=1) y se reintentaba (retry() → 'draft' → send() la
 * relanza), el job volvía a recorrer TODA la lista desde el principio --
 * los ya enviados en el intento anterior recibían el correo dos veces.
 */
class NewsletterCampaignRetryResumeTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function subscriber(string $email): Newsletter
    {
        return Newsletter::create([
            'slack' => (string) Str::uuid(),
            'email' => $email,
            'source' => 'manual',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
    }

    public function test_resuming_a_partially_sent_campaign_does_not_resend_to_already_sent_subscribers(): void
    {
        Mail::fake();

        $already = $this->subscriber('ya-recibio@example.com');
        $pending = $this->subscriber('pendiente@example.com');

        $campaign = NewsletterCampaign::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Campaña',
            'subject' => 'Asunto',
            'content' => '<p>Hola</p>',
            'status' => 'sending',
            'sent_count' => 1,
            'failed_count' => 0,
            // Checkpoint: ya se le envió a $already (id más bajo) en un
            // intento anterior que se cortó antes de llegar a $pending.
            'last_sent_newsletter_id' => $already->id,
            'created_by' => $this->manager->id,
        ]);

        (new SendNewsletterCampaignJob($campaign))->handle();

        // Un solo correo nuevo (Mail::fake() no captura el "to" real de
        // mailables que lo setean dentro de build(), así que se verifica el
        // comportamiento por sus efectos: el contador final es acumulado
        // -1 del intento anterior + 1 de este, no 1+2 si hubiera reenviado
        // a $already- y el checkpoint avanza exactamente al último
        // procesado ($pending), no queda en el de antes ($already).
        Mail::assertSentCount(1);
        $this->assertSame(2, $campaign->fresh()->sent_count);
        $this->assertSame($pending->id, $campaign->fresh()->last_sent_newsletter_id);
    }

    public function test_retry_endpoint_preserves_the_resume_checkpoint(): void
    {
        $subscriber = $this->subscriber('sub@example.com');

        $campaign = NewsletterCampaign::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Campaña',
            'subject' => 'Asunto',
            'content' => '<p>Hola</p>',
            'status' => 'failed',
            'sent_count' => 5,
            'failed_count' => 1,
            'last_sent_newsletter_id' => $subscriber->id,
            'created_by' => $this->manager->id,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.campaigns.retry', $campaign))
            ->assertOk()
            ->assertJsonPath('success', true);

        $campaign->refresh();
        $this->assertSame('draft', $campaign->status);
        // Antes: retry() reseteaba estos 3 campos a 0/null, perdiendo el
        // progreso y forzando un reenvío completo desde el principio.
        $this->assertSame(5, $campaign->sent_count);
        $this->assertSame(1, $campaign->failed_count);
        $this->assertSame($subscriber->id, $campaign->last_sent_newsletter_id);
    }
}
