<?php

namespace Tests\Feature\Managers\Newsletter;

use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regresión de dos hallazgos de la auditoría de newsletter:
 * - `SendNewsletterCampaignJob` debe despacharse en la cola dedicada
 *   `newsletter`, nunca en la `default` compartida con el resto de la app.
 * - `test()` (envío de prueba a una dirección arbitraria) exige
 *   `newsletters.update`; `newsletters.view` ya no debe ser suficiente.
 */
class NewsletterCampaignSendingTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
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

    public function test_send_campaign_job_is_pushed_onto_dedicated_newsletter_queue(): void
    {
        Queue::fake();

        Newsletter::create([
            'slack' => (string) Str::uuid(),
            'email' => 'sub@example.com',
            'source' => 'manual',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $campaign = $this->draftCampaign();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.campaigns.send', $campaign))
            ->assertOk();

        Queue::assertPushedOn('newsletter', SendNewsletterCampaignJob::class);
    }

    public function test_view_only_permission_cannot_trigger_test_send(): void
    {
        $viewer = User::factory()->manager()->create();
        $viewer->syncRoles([]);
        $viewer->givePermissionTo('newsletters.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $campaign = $this->draftCampaign();

        $this->actingAs($viewer->fresh())
            ->postJson(route('manager.newsletter.campaigns.test', $campaign), [
                'email' => 'destino@example.com',
            ])
            ->assertForbidden();
    }

    public function test_update_permission_can_trigger_test_send(): void
    {
        Mail::fake();

        $editor = User::factory()->manager()->create();
        $editor->syncRoles([]);
        $editor->givePermissionTo(['newsletters.view', 'newsletters.update']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $campaign = $this->draftCampaign();

        $this->actingAs($editor->fresh())
            ->postJson(route('manager.newsletter.campaigns.test', $campaign), [
                'email' => 'destino@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
