<?php

namespace Tests\Feature\Managers\Newsletter;

use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Mail\Newsletter\NewsletterCampaignMail;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterList;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cobertura de las listas/segmentos de campaña: CRUD manual, membresía
 * (alta/baja manual), protección de las listas dinámicas y el targeting de
 * campañas por lista.
 */
class NewsletterListTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function manualList(): NewsletterList
    {
        return NewsletterList::create([
            'slack' => (string) Str::uuid(),
            'name' => 'Lista manual',
            'trigger' => 'manual',
            'is_active' => true,
        ]);
    }

    private function dynamicList(): NewsletterList
    {
        return NewsletterList::create([
            'slack' => (string) Str::uuid(),
            'name' => 'Completaron un curso',
            'trigger' => 'course_completed',
            'is_active' => true,
        ]);
    }

    public function test_manager_can_view_lists_index(): void
    {
        $this->manualList();

        $this->actingAs($this->manager)
            ->get(route('manager.newsletter.lists.index'))
            ->assertOk();
    }

    public function test_manager_can_create_manual_list(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.lists.store'), [
                'name' => 'Clientes VIP',
                'is_active' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('newsletter_lists', [
            'name' => 'Clientes VIP',
            'trigger' => 'manual',
        ]);
    }

    public function test_manager_can_add_member_by_email_idempotently(): void
    {
        $list = $this->manualList();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.lists.members.add', $list->id), [
                'email' => 'nuevo@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        // Segunda alta del mismo email: no duplica.
        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.lists.members.add', $list->id), [
                'email' => 'nuevo@example.com',
            ])
            ->assertOk();

        $this->assertSame(1, $list->fresh()->subscribers()->count());
        $this->assertDatabaseHas('newsletters', [
            'email' => 'nuevo@example.com',
            'source' => 'lifecycle',
        ]);
    }

    public function test_add_member_rejects_invalid_email(): void
    {
        $list = $this->manualList();

        $this->actingAs($this->manager)
            ->postJson(route('manager.newsletter.lists.members.add', $list->id), [
                'email' => 'no-es-email',
            ])
            ->assertStatus(422);
    }

    public function test_manager_can_remove_member_from_list(): void
    {
        $list = $this->manualList();
        $list->addByEmail('quitar@example.com');
        $subscriber = Newsletter::where('email', 'quitar@example.com')->first();

        $this->actingAs($this->manager)
            ->deleteJson(route('manager.newsletter.lists.members.remove', [$list->id, $subscriber->id]))
            ->assertOk();

        $this->assertSame(0, $list->fresh()->subscribers()->count());
    }

    public function test_dynamic_list_cannot_be_deleted(): void
    {
        $list = $this->dynamicList();

        $this->actingAs($this->manager)
            ->delete(route('manager.newsletter.lists.destroy', $list->id))
            ->assertRedirect();

        $this->assertDatabaseHas('newsletter_lists', ['id' => $list->id]);
    }

    public function test_manual_list_can_be_deleted(): void
    {
        $list = $this->manualList();

        $this->actingAs($this->manager)
            ->delete(route('manager.newsletter.lists.destroy', $list->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('newsletter_lists', ['id' => $list->id]);
    }

    public function test_add_by_email_is_idempotent_and_tags_source(): void
    {
        $list = $this->manualList();

        $list->addByEmail('a@example.com', 'Ana', 'motivo');
        $list->addByEmail('a@example.com', 'Ana', 'motivo');

        $this->assertSame(1, $list->subscribers()->count());
        $subscriber = Newsletter::where('email', 'a@example.com')->first();
        $this->assertSame('lifecycle', $subscriber->source);
        $this->assertTrue((bool) $subscriber->is_active);
    }

    public function test_campaign_with_list_targets_only_its_active_members(): void
    {
        Mail::fake();

        $list = $this->manualList();
        $list->addByEmail('m1@example.com');
        $list->addByEmail('m2@example.com');
        $list->addByEmail('baja@example.com');
        // Un miembro dado de baja no debe recibir.
        Newsletter::where('email', 'baja@example.com')->update(['is_active' => false]);

        // Un suscriptor general FUERA de la lista no debe recibir.
        Newsletter::create([
            'slack' => (string) Str::uuid(),
            'email' => 'fuera@example.com',
            'source' => 'manual',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $campaign = NewsletterCampaign::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Campaña lista',
            'subject' => 'Asunto',
            'content' => '<p>hola</p>',
            'status' => 'sending',
            'newsletter_list_id' => $list->id,
        ]);

        (new SendNewsletterCampaignJob($campaign))->handle();

        // Solo los 2 miembros activos de la lista.
        Mail::assertSent(NewsletterCampaignMail::class, 2);
    }
}
