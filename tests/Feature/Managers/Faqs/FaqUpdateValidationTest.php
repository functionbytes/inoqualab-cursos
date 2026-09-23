<?php

namespace Tests\Feature\Managers\Faqs;

use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategorie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: FaqsController::update() (Managers Y Supports, comparten el
 * mismo modelo/permiso) usaba `Request $request` plano -- sin ningún
 * FormRequest ni validación inline. `faqs.category_id` es NOT NULL sin
 * default en BD; un POST sin `categorie` (o con un id que no existe)
 * pasaba directo al UPDATE y revenía con un 500 real (Integrity constraint
 * violation) en vez de un 422 legible. Mismo patrón ya visto y corregido
 * antes en Testimonies/Sliders/Instructions/Enterprises/Courses.
 */
class FaqUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected FaqCategorie $category;

    protected Faq $faq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();

        $this->category = new FaqCategorie;
        $this->category->slack = Str::random(10);
        $this->category->title = 'General';
        $this->category->slug = 'general-'.Str::random(4);
        $this->category->available = 1;
        $this->category->save();

        $this->faq = new Faq;
        $this->faq->slack = Str::random(10);
        $this->faq->title = 'Pregunta original';
        $this->faq->slug = 'pregunta-original-'.Str::random(4);
        $this->faq->available = 1;
        $this->faq->category_id = $this->category->id;
        $this->faq->save();
    }

    public function test_manager_update_rejects_missing_category(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.faqs.update'), [
                'slack' => $this->faq->slack,
                'title' => 'Pregunta actualizada',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categorie');

        $this->assertSame('Pregunta original', $this->faq->fresh()->title);
    }

    public function test_manager_update_rejects_nonexistent_category(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.faqs.update'), [
                'slack' => $this->faq->slack,
                'title' => 'Pregunta actualizada',
                'available' => 1,
                'categorie' => $this->category->id + 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categorie');
    }

    public function test_manager_update_accepts_valid_data(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.faqs.update'), [
                'slack' => $this->faq->slack,
                'title' => 'Pregunta actualizada',
                'available' => 1,
                'categorie' => $this->category->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('Pregunta actualizada', $this->faq->fresh()->title);
    }

    public function test_support_update_rejects_missing_category(): void
    {
        $support = User::factory()->create(['role' => 'support']);

        $this->actingAs($support)
            ->postJson(route('support.faqs.update'), [
                'slack' => $this->faq->slack,
                'title' => 'Pregunta actualizada',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categorie');
    }
}
