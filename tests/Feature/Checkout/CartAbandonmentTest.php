<?php

namespace Tests\Feature\Checkout;

use App\Console\Commands\Orders\RemindIncompleteCarts;
use App\Mail\Customers\Orders\IncompleteCartMail;
use App\Models\CartAbandonment;
use App\Models\Course\Course;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartAbandonmentTest extends TestCase
{
    use RefreshDatabase;

    protected function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], [
            'slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online',
        ]);
        OrderMethod::firstOrCreate(['slug' => 'card'], [
            'slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card',
        ]);
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            \DB::table('order_condition')->insertOrIgnore([
                'id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug,
            ]);
        }
    }

    private function makeUser(): User
    {
        return User::factory()->create(['role' => 'customer', 'available' => 1, 'validation' => 1]);
    }

    private function makeCourse(float $price = 100000): Course
    {
        return Course::factory()->create(['price' => $price, 'payment' => 1, 'promotion' => 0, 'available' => 1]);
    }

    private function cartWithCourse(Course $course): array
    {
        return [
            'course_'.$course->slack => [
                'type' => 'course', 'slack' => $course->slack, 'title' => $course->title,
                'price' => $course->price, 'qty' => 1,
            ],
        ];
    }

    public function test_capture_lead_creates_abandonment_with_non_empty_cart(): void
    {
        $course = $this->makeCourse(50000);

        $this->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('cart.capture-lead'), ['email' => 'lead@example.com'])
            ->assertOk()
            ->assertJson(['success' => true, 'tracked' => true]);

        $abandonment = CartAbandonment::where('email', 'lead@example.com')->first();
        $this->assertNotNull($abandonment);
        $this->assertSame(50000.0, (float) $abandonment->total);
        $this->assertCount(1, $abandonment->items);
        $this->assertNull($abandonment->converted_at);
    }

    public function test_capture_lead_does_nothing_with_empty_cart(): void
    {
        $this->withSession(['cart' => []])
            ->postJson(route('cart.capture-lead'), ['email' => 'empty@example.com'])
            ->assertOk()
            ->assertJson(['success' => true, 'tracked' => false]);

        $this->assertDatabaseMissing('cart_abandonments', ['email' => 'empty@example.com']);
    }

    public function test_capture_lead_rejects_invalid_email(): void
    {
        $course = $this->makeCourse();

        $this->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('cart.capture-lead'), ['email' => 'not-an-email'])
            ->assertUnprocessable();
    }

    public function test_authenticated_generate_marks_matching_abandonment_as_converted(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);

        CartAbandonment::create([
            'slack' => 'abcdef',
            'email' => mb_strtolower($user->email),
            'user_id' => $user->id,
            'items' => [['type' => 'course', 'slack' => $course->slack, 'title' => $course->title, 'amount' => 50000]],
            'total' => 50000,
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('checkout.generate'))
            ->assertOk();

        $this->assertNotNull(
            CartAbandonment::where('email', mb_strtolower($user->email))->first()->converted_at
        );
    }

    public function test_restore_repopulates_cart_session_and_redirects_to_checkout(): void
    {
        $course = $this->makeCourse(50000);

        $abandonment = CartAbandonment::create([
            'slack' => 'restor1',
            'email' => 'restore@example.com',
            'items' => [['type' => 'course', 'slack' => $course->slack, 'title' => $course->title, 'unit' => 50000, 'qty' => 1]],
            'total' => 50000,
        ]);

        $response = $this->get(route('cart.restore', $abandonment->slack));

        $response->assertRedirect(route('checkout.cart'));
        $this->assertNotEmpty(session('cart'));
        $this->assertArrayHasKey('course_'.$course->slack, session('cart'));
    }

    public function test_restore_redirects_when_slack_not_found(): void
    {
        $this->get(route('cart.restore', 'doesnotexist'))
            ->assertRedirect(route('courses'));
    }

    public function test_restore_ignores_already_converted_abandonment(): void
    {
        $course = $this->makeCourse();

        $abandonment = CartAbandonment::create([
            'slack' => 'conv001',
            'email' => 'converted@example.com',
            'items' => [['type' => 'course', 'slack' => $course->slack, 'title' => $course->title, 'unit' => 50000, 'qty' => 1]],
            'total' => 50000,
            'converted_at' => now(),
        ]);

        $this->get(route('cart.restore', $abandonment->slack))
            ->assertRedirect(route('courses'));
    }

    public function test_remind_incomplete_carts_sends_only_pending_past_cutoff(): void
    {
        Mail::fake();

        // created_at no está en $fillable (a propósito: en producción siempre es
        // "ahora"), así que para simular una captura vieja se asigna la propiedad
        // directo y se guarda -- create() con 'created_at' en el array lo ignora
        // silenciosamente y el registro queda con la fecha real de hoy.
        $old = CartAbandonment::create([
            'slack' => 'old0001', 'email' => 'old@example.com',
            'items' => [['type' => 'course', 'slack' => 'x', 'title' => 'X', 'amount' => 10000]],
            'total' => 10000,
        ]);
        $old->created_at = now()->subHours(3);
        $old->save();

        $recent = CartAbandonment::create([
            'slack' => 'new0001', 'email' => 'recent@example.com',
            'items' => [['type' => 'course', 'slack' => 'y', 'title' => 'Y', 'amount' => 10000]],
            'total' => 10000,
        ]);

        $this->artisan(RemindIncompleteCarts::class, ['--hours' => 1])->assertSuccessful();

        Mail::assertQueued(IncompleteCartMail::class, fn ($mail) => $mail->email === 'old@example.com');
        Mail::assertNotQueued(IncompleteCartMail::class, fn ($mail) => $mail->email === 'recent@example.com');

        $this->assertNotNull($old->fresh()->reminded_at);
        $this->assertNull($recent->fresh()->reminded_at);
    }

    public function test_remind_incomplete_carts_skips_converted_and_already_reminded(): void
    {
        Mail::fake();

        $converted = CartAbandonment::create([
            'slack' => 'conv0002', 'email' => 'converted2@example.com',
            'items' => [['type' => 'course', 'slack' => 'x', 'title' => 'X', 'amount' => 10000]],
            'total' => 10000, 'converted_at' => now(),
        ]);
        $converted->created_at = now()->subHours(3);
        $converted->save();

        $reminded = CartAbandonment::create([
            'slack' => 'remind01', 'email' => 'already@example.com',
            'items' => [['type' => 'course', 'slack' => 'x', 'title' => 'X', 'amount' => 10000]],
            'total' => 10000, 'reminded_at' => now()->subMinutes(30),
        ]);
        $reminded->created_at = now()->subHours(3);
        $reminded->save();

        $this->artisan(RemindIncompleteCarts::class, ['--hours' => 1])->assertSuccessful();

        Mail::assertNothingQueued();
    }
}
