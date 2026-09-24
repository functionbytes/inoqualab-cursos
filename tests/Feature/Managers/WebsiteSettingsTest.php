<?php

namespace Tests\Feature\Managers;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WebsiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        Permission::findOrCreate('settings.update', 'web');

        $manager = User::factory()->manager()->create();
        $manager->givePermissionTo('settings.update');

        return $manager->fresh();
    }

    private function renderCard(Course $course): string
    {
        $course->loadMissing(['categorie', 'media'])->loadCount(['lessons', 'chapters']);

        return view('pages.partials.components.course-card', ['course' => $course])->render();
    }

    public function test_manager_can_open_website_settings(): void
    {
        // La vista previa usa un curso publicado en el sitio (available + website).
        Course::factory()->onSale()->create(['website' => 1]);

        $this->actingAs($this->manager())
            ->get('/panel/settings/website')
            ->assertOk()
            ->assertSee('Tarjeta de curso')
            ->assertSee('Hermana del paquete')
            ->assertSee('Precio sobre la imagen')
            ->assertSee('Ficha con datos')
            ->assertSee('Base oscura corporativa')
            ->assertSee('Detalle de curso')
            ->assertSee('Editorial con imagen y ruta')
            ->assertSee('Placa de Petri')
            ->assertSee('crs-card--a', false)
            ->assertSee('crs-card--d', false);
    }

    public function test_manager_can_switch_course_card_variant(): void
    {
        $this->actingAs($this->manager())
            ->post('/panel/settings/website/update', ['pages_course_card_variant' => 'b', 'pages_course_detail_variant' => '1', 'pages_about_variant' => '1'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('settings', [
            'key' => 'pages_course_card_variant',
            'value' => 'b',
        ]);
    }

    /** El valor compone el nombre del parcial: nada fuera de a/b/c/d llega a la tabla. */
    public function test_invalid_variant_is_rejected(): void
    {
        $this->actingAs($this->manager())
            ->postJson('/panel/settings/website/update', ['pages_course_card_variant' => '../../layouts/pages', 'pages_course_detail_variant' => '1', 'pages_about_variant' => '1'])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('settings', ['key' => 'pages_course_card_variant']);
    }

    public function test_customer_cannot_open_website_settings(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get('/panel/settings/website')
            ->assertRedirect();
    }

    public function test_card_defaults_to_variant_c(): void
    {
        _settingsCache(null, true);

        $this->assertStringContainsString('crs-card--c', $this->renderCard(Course::factory()->create()));
    }

    public function test_card_renders_the_chosen_variant(): void
    {
        updateSettings(['pages_course_card_variant' => 'd']);

        $html = $this->renderCard(Course::factory()->onSale()->create());

        $this->assertStringContainsString('crs-card--d', $html);
        $this->assertStringContainsString('-20%', $html);
    }

    /** Un valor inválido guardado a mano en la BD no debe tumbar el catálogo. */
    public function test_card_falls_back_to_c_for_an_unknown_stored_value(): void
    {
        updateSettings(['pages_course_card_variant' => 'z']);

        $this->assertStringContainsString('crs-card--c', $this->renderCard(Course::factory()->create()));
    }

    public function test_manager_can_switch_course_detail_variant(): void
    {
        $this->actingAs($this->manager())
            ->post('/panel/settings/website/update', ['pages_course_card_variant' => 'c', 'pages_course_detail_variant' => '2', 'pages_about_variant' => '1'])
            ->assertOk();

        $this->assertDatabaseHas('settings', ['key' => 'pages_course_detail_variant', 'value' => '2']);
    }

    public function test_invalid_detail_variant_is_rejected(): void
    {
        $this->actingAs($this->manager())
            ->postJson('/panel/settings/website/update', ['pages_course_card_variant' => 'c', 'pages_course_detail_variant' => '3', 'pages_about_variant' => '1'])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('settings', ['key' => 'pages_course_detail_variant']);
    }

    public function test_course_detail_uses_modality_1_by_default(): void
    {
        _settingsCache(null, true);
        $course = Course::factory()->create(['website' => 1]);

        $this->get(route('courses.view', $course->slack))
            ->assertOk()
            ->assertViewIs('pages.views.courses.view');
    }

    public function test_course_detail_uses_modality_2_when_chosen(): void
    {
        updateSettings(['pages_course_detail_variant' => '2', 'pages_about_variant' => '1']);
        $course = Course::factory()->onSale()->create(['website' => 1]);

        $this->get(route('courses.view', $course->slack))
            ->assertOk()
            ->assertViewIs('pages.views.courses.view-editorial')
            ->assertSee('cde-hero', false)
            ->assertSee('Comprar ahora');
    }

    public function test_about_page_uses_the_original_design_by_default(): void
    {
        _settingsCache(null, true);

        $this->get('/about')->assertOk()->assertViewIs('pages.views.about');
    }

    public function test_about_page_uses_the_chosen_design(): void
    {
        foreach (['a' => 'informe', 'b' => 'petri', 'c' => 'norma', 'd' => 'combinada'] as $value => $view) {
            updateSettings(['pages_about_variant' => $value]);

            $this->get('/about')
                ->assertOk()
                ->assertViewIs('pages.views.about.'.$view)
                ->assertSee('Misión')
                ->assertSee('Visión');
        }
    }

    public function test_manager_can_preview_an_about_design_without_changing_it(): void
    {
        _settingsCache(null, true);

        $this->actingAs($this->manager())
            ->get('/about?diseno=b')
            ->assertOk()
            ->assertViewIs('pages.views.about.petri');

        $this->assertDatabaseMissing('settings', ['key' => 'pages_about_variant']);
    }

    public function test_visitors_cannot_use_the_about_preview(): void
    {
        _settingsCache(null, true);

        $this->get('/about?diseno=b')->assertOk()->assertViewIs('pages.views.about');
    }

    public function test_invalid_about_variant_is_rejected(): void
    {
        $this->actingAs($this->manager())
            ->postJson('/panel/settings/website/update', ['pages_course_card_variant' => 'c', 'pages_course_detail_variant' => '1', 'pages_about_variant' => 'z'])
            ->assertUnprocessable();
    }
}
