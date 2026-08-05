<?php

namespace Tests\Feature\Customers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * customers.notifications.view era la única ruta de todo el portal customer
 * que ningún smoke test lograba ejercitar (necesita una notificación real con
 * UUID, no un modelo con slack). Sin cobertura dedicada previa.
 */
class NotificationsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function insertNotification(User $user, ?string $readAt = null): string
    {
        $id = Str::uuid()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Título', 'message' => 'Mensaje de prueba']),
            'read_at' => $readAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_view_marks_the_notification_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $id = $this->insertNotification($customer);

        $this->actingAs($customer)
            ->get(route('customers.notifications.view', $id))
            ->assertOk();

        $this->assertNotNull(DB::table('notifications')->where('id', $id)->value('read_at'));
    }

    public function test_customer_cannot_view_another_customers_notification(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $intruder = User::factory()->create(['role' => 'customer']);
        $id = $this->insertNotification($owner);

        $this->actingAs($intruder)
            ->get(route('customers.notifications.view', $id))
            ->assertNotFound();

        $this->assertNull(DB::table('notifications')->where('id', $id)->value('read_at'));
    }

    public function test_mark_marks_a_single_notification_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $id = $this->insertNotification($customer);
        $other = $this->insertNotification($customer);

        $this->actingAs($customer)
            ->post(route('customers.notifications.mark'), ['id' => $id])
            ->assertNoContent();

        $this->assertNotNull(DB::table('notifications')->where('id', $id)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $other)->value('read_at'), 'No debe marcar otras notificaciones.');
    }

    public function test_mark_without_id_marks_all_unread_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $first = $this->insertNotification($customer);
        $second = $this->insertNotification($customer);

        $this->actingAs($customer)
            ->post(route('customers.notifications.mark'), [])
            ->assertNoContent();

        $this->assertNotNull(DB::table('notifications')->where('id', $first)->value('read_at'));
        $this->assertNotNull(DB::table('notifications')->where('id', $second)->value('read_at'));
    }

    public function test_delete_removes_the_notification(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $id = $this->insertNotification($customer);

        $this->actingAs($customer)
            ->delete(route('customers.notifications.delete'), ['id' => $id])
            ->assertOk()
            ->assertJsonPath('success', 'Borrado exitosamente');

        $this->assertDatabaseMissing('notifications', ['id' => $id]);
    }

    public function test_delete_of_unknown_notification_returns_404(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->delete(route('customers.notifications.delete'), ['id' => (string) Str::uuid()])
            ->assertNotFound();
    }

    public function test_customer_cannot_delete_another_customers_notification(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $intruder = User::factory()->create(['role' => 'customer']);
        $id = $this->insertNotification($owner);

        $this->actingAs($intruder)
            ->delete(route('customers.notifications.delete'), ['id' => $id])
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', ['id' => $id]);
    }

    public function test_search_filters_by_title(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $customer->id,
            'data' => json_encode(['title' => 'Curso completado', 'message' => 'x']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $customer->id,
            'data' => json_encode(['title' => 'Recordatorio de pago', 'message' => 'x']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('customers.notifications.search'), ['search' => 'completado'])
            ->assertOk()
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Curso completado') && ! str_contains($html, 'Recordatorio de pago'));
    }
}
