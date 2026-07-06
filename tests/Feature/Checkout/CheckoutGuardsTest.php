<?php

namespace Tests\Feature\Checkout;

use App\Models\Course\Course;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Guards de seguridad/dinero del checkout añadidos tras la auditoría: curso no
 * disponible no vendible, qty de curso forzado a 1, y total_after_discount
 * asignado (para que la venta no aparezca en $0 en contabilidad).
 */
class CheckoutGuardsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);
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

    private function makeCourse(float $price, int $available = 1): Course
    {
        return Course::factory()->create([
            'price' => $price, 'payment' => 1, 'promotion' => 0, 'available' => $available,
        ]);
    }

    private function cartLine(Course $course, int $qty = 1): array
    {
        return [
            'course_'.$course->slack => [
                'type' => 'course', 'slack' => $course->slack,
                'title' => $course->title, 'price' => $course->price, 'qty' => $qty,
            ],
        ];
    }

    public function test_generate_sets_total_after_discount(): void
    {
        Mail::fake();
        $this->seedLookups();
        $user = $this->makeUser();
        $course = $this->makeCourse(50000);

        $this->actingAs($user)
            ->withSession(['cart' => $this->cartLine($course)])
            ->postJson(route('checkout.generate'))
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_order_amount' => 50000,
            'total_after_discount' => 50000,
        ]);
    }

    public function test_unavailable_course_cannot_be_purchased(): void
    {
        $this->seedLookups();
        $user = $this->makeUser();
        $hidden = $this->makeCourse(50000, available: 0);

        // Carrito con solo el curso oculto → sin líneas válidas → 422.
        $this->actingAs($user)
            ->withSession(['cart' => $this->cartLine($hidden)])
            ->postJson(route('checkout.generate'))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }

    public function test_course_quantity_is_forced_to_one(): void
    {
        Mail::fake();
        $this->seedLookups();
        $user = $this->makeUser();
        $course = $this->makeCourse(50000);

        // qty=3 en el request, pero un curso se cobra una sola vez.
        $this->actingAs($user)
            ->withSession(['cart' => $this->cartLine($course, qty: 3)])
            ->postJson(route('checkout.generate'))
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_order_amount' => 50000,
        ]);
    }
}
