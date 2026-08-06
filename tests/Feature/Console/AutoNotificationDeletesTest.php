<?php

namespace Tests\Feature\Console;

use App\Models\Setting\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión de notification:autodelete. El comando consultaba
 * User::with('notifications')->chunk(...) -- User usa SoftDeletes, así que el
 * scope global excluía usuarios eliminados y sus notificaciones nunca se
 * purgaban, acumulándose indefinidamente en la tabla.
 */
class AutoNotificationDeletesTest extends TestCase
{
    use RefreshDatabase;

    private function enableAutoDelete(int $days = 30): void
    {
        Setting::query()->create(['key' => 'AUTO_NOTIFICATION_DELETE_ENABLE', 'value' => 'on']);
        Setting::query()->create(['key' => 'AUTO_NOTIFICATION_DELETE_DAYS', 'value' => (string) $days]);
    }

    private function createReadNotification(User $user, int $readDaysAgo): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'test']),
            'read_at' => now()->subDays($readDaysAgo),
            'created_at' => now()->subDays($readDaysAgo),
            'updated_at' => now()->subDays($readDaysAgo),
        ]);

        return $id;
    }

    public function test_purges_read_notifications_of_soft_deleted_users(): void
    {
        $this->enableAutoDelete(days: 30);

        $user = User::factory()->create(['role' => 'customer']);
        $notificationId = $this->createReadNotification($user, readDaysAgo: 45);

        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $this->artisan('notification:autodelete')->assertSuccessful();

        $this->assertDatabaseMissing('notifications', ['id' => $notificationId]);
    }

    public function test_does_not_purge_notifications_still_within_the_retention_window(): void
    {
        $this->enableAutoDelete(days: 30);

        $user = User::factory()->create(['role' => 'customer']);
        $notificationId = $this->createReadNotification($user, readDaysAgo: 5);

        $this->artisan('notification:autodelete')->assertSuccessful();

        $this->assertDatabaseHas('notifications', ['id' => $notificationId]);
    }
}
