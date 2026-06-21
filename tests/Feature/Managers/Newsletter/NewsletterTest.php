<?php

namespace Tests\Feature\Managers\Newsletter;

use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Mail\Newsletter\UnsubscribedMail;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\User;
use App\Services\NewsletterMailjetService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Cobertura de escritura del Newsletter: alta de suscriptores, acciones
 * masivas y campañas. Verifica además que el envío de campañas exige
 * `newsletters.update` (no basta `newsletters.view`).
 */
class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        Http::fake(); // evita llamadas reales a Mailjet
    }

    private function subscriber(array $overrides = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'slack' => (string) Str::uuid(),
            'email' => 's'.Str::random(6).'@example.com',
            'name' => 'Sub',
            'source' => 'manual',
            'is_active' => true,
            'subscribed_at' => now(),
        ], $overrides));
    }

    private function draftCampaign(): NewsletterCampaign
    {
        return NewsletterCampaign::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Campaña',
            'subject' => 'Asunto',
            'content' => '<p>Hola</p>',
            'status' => 'draft',
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_manager_can_add_subscriber(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.store'), ['email' => 'nuevo@example.com', 'name' => 'Nuevo'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('newsletters', ['email' => 'nuevo@example.com']);
    }

    public function test_manager_can_bulk_delete_subscribers(): void
    {
        $a = $this->subscriber();
        $b = $this->subscriber();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertDatabaseMissing('newsletters', ['id' => $a->id]);
        $this->assertDatabaseMissing('newsletters', ['id' => $b->id]);
    }

    public function test_bulk_delete_removes_contacts_from_mailjet(): void
    {
        $a = $this->subscriber();
        $b = $this->subscriber();

        // Regresión: bulkDelete dejaba contactos fantasma en Mailjet.
        $mock = $this->mock(NewsletterMailjetService::class);
        $mock->shouldReceive('removeContact')->twice();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertDatabaseCount('newsletters', 0);
    }

    public function test_bulk_unsubscribe_updates_db_and_notifies(): void
    {
        Mail::fake();
        $a = $this->subscriber();
        $b = $this->subscriber();

        $mock = $this->mock(NewsletterMailjetService::class);
        $mock->shouldReceive('removeContact')->twice();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.bulk-action'), ['action' => 'unsubscribe', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertFalse($a->fresh()->is_active);
        $this->assertFalse($b->fresh()->is_active);
        Mail::assertQueued(UnsubscribedMail::class, 2);
    }

    public function test_manager_can_create_campaign(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.newsletter.campaigns.store'), [
                'name' => 'Camp', 'subject' => 'Asunto', 'content' => '<p>Hola</p>',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('newsletter_campaigns', ['name' => 'Camp', 'status' => 'draft']);
    }

    public function test_send_campaign_dispatches_job(): void
    {
        Queue::fake();
        $this->subscriber();
        $campaign = $this->draftCampaign();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.campaigns.send', $campaign))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('sending', $campaign->fresh()->status);
        Queue::assertPushed(SendNewsletterCampaignJob::class);
    }

    public function test_cannot_send_campaign_without_subscribers(): void
    {
        Queue::fake();
        $campaign = $this->draftCampaign();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.campaigns.send', $campaign))
            ->assertStatus(422);

        Queue::assertNotPushed(SendNewsletterCampaignJob::class);
    }

    public function test_send_campaign_requires_update_permission(): void
    {
        // Escalada cerrada: newsletters.view ya no basta para disparar un envío masivo.
        Queue::fake();
        $this->subscriber();

        $viewer = User::factory()->manager()->create();
        $viewer->syncRoles([]);
        $viewer->givePermissionTo('newsletters.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $campaign = $this->draftCampaign();

        $this->actingAs($viewer->fresh())
            ->postJson(route('manager.newsletter.campaigns.send', $campaign))
            ->assertForbidden();

        Queue::assertNotPushed(SendNewsletterCampaignJob::class);
    }
}
