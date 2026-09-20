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
use Illuminate\Http\UploadedFile;
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

    // ── Ronda 3: ciclo individual (toggle/resend/destroy) sin cobertura previa ──

    public function test_toggle_unsubscribes_an_active_subscriber(): void
    {
        $mock = $this->mock(NewsletterMailjetService::class);
        $mock->shouldReceive('removeContact')->once();

        $subscriber = $this->subscriber(['is_active' => true]);

        $this->actingAs($this->manager)
            ->patchJson(route('manager.newsletter.toggle', $subscriber))
            ->assertOk();

        $this->assertFalse($subscriber->fresh()->is_active);
        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_toggle_resubscribes_an_inactive_subscriber(): void
    {
        $mock = $this->mock(NewsletterMailjetService::class);
        $mock->shouldReceive('addContact')->once();

        $subscriber = $this->subscriber(['is_active' => false]);

        $this->actingAs($this->manager)
            ->patchJson(route('manager.newsletter.toggle', $subscriber))
            ->assertOk();

        $this->assertTrue($subscriber->fresh()->is_active);
    }

    public function test_resend_confirmation_sends_mail_for_pending_subscriber(): void
    {
        Mail::fake();
        $subscriber = $this->subscriber(['is_active' => false, 'confirmation_token' => 'old-token']);

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.resend-confirmation', $subscriber))
            ->assertOk();

        $this->assertNotSame('old-token', $subscriber->fresh()->confirmation_token);
    }

    public function test_resend_confirmation_rejects_a_subscriber_that_is_not_pending(): void
    {
        $subscriber = $this->subscriber(['is_active' => true, 'confirmation_token' => null]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.resend-confirmation', $subscriber))
            ->assertStatus(422);
    }

    public function test_manager_can_destroy_a_single_subscriber(): void
    {
        $mock = $this->mock(NewsletterMailjetService::class);
        $mock->shouldReceive('removeContact')->once();

        $subscriber = $this->subscriber();

        $this->actingAs($this->manager)
            ->delete(route('manager.newsletter.destroy', $subscriber))
            ->assertRedirect();

        $this->assertDatabaseMissing('newsletters', ['id' => $subscriber->id]);
    }

    public function test_destroy_requires_delete_permission_not_just_update(): void
    {
        $subscriber = $this->subscriber();

        $limited = User::factory()->manager()->create();
        $limited->syncRoles([]);
        $limited->syncPermissions(['newsletters.view', 'newsletters.update']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($limited->fresh())
            ->delete(route('manager.newsletter.destroy', $subscriber))
            ->assertForbidden();

        $this->assertDatabaseHas('newsletters', ['id' => $subscriber->id]);
    }

    public function test_manager_can_import_subscribers_via_csv(): void
    {
        $csv = "email,name\nqa-import-a@example.com,QA Import A\nqa-import-b@example.com,QA Import B\n";
        $file = UploadedFile::fake()->createWithContent('subscribers.csv', $csv);

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.import'), ['file' => $file])
            ->assertOk()
            ->assertJson(['success' => true, 'imported' => 2, 'skipped' => 0, 'errors' => 0]);

        $this->assertDatabaseHas('newsletters', ['email' => 'qa-import-a@example.com', 'source' => 'import']);
        $this->assertDatabaseHas('newsletters', ['email' => 'qa-import-b@example.com', 'source' => 'import']);
    }

    public function test_export_streams_a_csv_with_the_expected_header(): void
    {
        $this->subscriber(['email' => 'qa-export@example.com']);

        $response = $this->actingAs($this->manager)->get(route('manager.newsletter.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('qa-export@example.com', $content);
        $this->assertStringContainsString('ID,Email,Nombre,Estado', $content);
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
