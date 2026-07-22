<?php

namespace Tests\Feature\Notifications;

use App\Models\Mail\IncomingMail;
use App\Models\User;
use App\Notifications\IncomingMail\UnprocessableMailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** Inserta N notificaciones de base de datos para un usuario. */
    private function seedNotifications(User $user, int $count): void
    {
        foreach (range(1, $count) as $i) {
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\Test',
                'data' => ['type' => 'test', 'title' => "Noti {$i}", 'message' => 'x'],
                'read_at' => null,
            ]);
        }
    }

    public function test_customer_index_returns_more_than_ten_notifications(): void
    {
        // Regresión: index() hacía paginate(10)->groupBy(), que descartaba el
        // paginador y limitaba la vista a 10 notificaciones sin navegación.
        $user = User::factory()->customer()->create();
        $this->seedNotifications($user, 15);

        $response = $this->actingAs($user)->get(route('customers.notifications'));

        $response->assertOk();
        $grouped = $response->viewData('notifications');
        $total = collect($grouped)->flatten(1)->count();
        $this->assertSame(15, $total, 'Deben mostrarse las 15 notificaciones, no solo 10.');
    }

    public function test_opening_a_notification_marks_it_as_read(): void
    {
        $user = User::factory()->customer()->create();
        $this->seedNotifications($user, 1);
        $notification = $user->notifications()->first();
        $this->assertNull($notification->read_at);

        $this->actingAs($user)->get(route('customers.notifications.view', $notification->id))->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_open_another_users_notification(): void
    {
        $owner = User::factory()->customer()->create();
        $this->seedNotifications($owner, 1);
        $notification = $owner->notifications()->first();

        $intruder = User::factory()->customer()->create();

        $this->actingAs($intruder)
            ->get(route('customers.notifications.view', $notification->id))
            ->assertNotFound();
    }

    public function test_unprocessable_mail_notification_has_real_action_url(): void
    {
        // El TODO dejaba action_url en '#'; ahora enlaza a la bandeja de correos.
        $mail = IncomingMail::factory()->create();
        $user = User::factory()->create(['role' => 'support']);

        $data = (new UnprocessableMailNotification($mail))->toArray($user);

        $this->assertSame('incoming_mail_failed', $data['type']);
        $this->assertNotSame('#', $data['action_url']);
        $this->assertStringContainsString($mail->slack, $data['action_url']);
    }
}
