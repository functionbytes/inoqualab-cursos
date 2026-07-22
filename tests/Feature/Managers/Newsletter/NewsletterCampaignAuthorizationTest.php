<?php

namespace Tests\Feature\Managers\Newsletter;

use App\Models\NewsletterCampaign;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Blinda los guards de permiso añadidos a test/duplicate/retry (antes cualquier
 * manager podía usar `test` como relay de correo, o duplicar/reintentar sin permiso).
 */
class NewsletterCampaignAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeCampaign(string $status = 'draft'): NewsletterCampaign
    {
        return NewsletterCampaign::create([
            'uid' => Str::uuid(),
            'name' => 'Campaña '.Str::random(4),
            'subject' => 'Asunto',
            'content' => '<p>Contenido</p>',
            'status' => $status,
        ]);
    }

    public function test_test_send_forbidden_without_update_permission(): void
    {
        Mail::fake();
        $campaign = $this->makeCampaign();
        Role::findByName('manager')->revokePermissionTo('newsletters.update');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.newsletter.campaigns.test', $campaign), ['email' => 'x@test.com'])
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_duplicate_forbidden_without_create_permission(): void
    {
        $campaign = $this->makeCampaign();
        Role::findByName('manager')->revokePermissionTo('newsletters.create');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.newsletter.campaigns.duplicate', $campaign))
            ->assertForbidden();

        $this->assertSame(1, NewsletterCampaign::count());
    }

    public function test_retry_forbidden_without_update_permission(): void
    {
        $campaign = $this->makeCampaign('failed');
        Role::findByName('manager')->revokePermissionTo('newsletters.update');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.newsletter.campaigns.retry', $campaign))
            ->assertForbidden();
    }
}
