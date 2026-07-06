<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\CourseCategorie;

/**
 * Cubre los hallazgos críticos de la auditoría de Categorías de curso:
 * - validación ausente en store/update (título vacío se guardaba tal cual)
 * - lectura sin permiso Spatie (index/create/edit/view no verificaban nada)
 * - vista de detalle referenciando columnas inexistentes ($categorie->name/description)
 */
class CourseCategoriesTest extends CategoriesTestCase
{
    public function test_store_rejects_empty_title(): void
    {
        $manager = $this->managerWithPermissions();

        $response = $this->actingAs($manager)->post(route('manager.categories.courses.store'), [
            'title' => '',
            'available' => '1',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseMissing('course_categories', ['title' => '']);
    }

    public function test_update_rejects_empty_title(): void
    {
        $manager = $this->managerWithPermissions();
        $categorie = CourseCategorie::create([
            'slack' => 'cat-original',
            'title' => 'Programacion',
            'slug' => 'programacion',
            'available' => 1,
        ]);

        $response = $this->actingAs($manager)->post(route('manager.categories.courses.update'), [
            'slack' => $categorie->slack,
            'title' => '',
            'available' => '1',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertSame('Programacion', $categorie->fresh()->title);
    }

    public function test_store_creates_category_with_valid_title(): void
    {
        $manager = $this->managerWithPermissions();

        $response = $this->actingAs($manager)->post(route('manager.categories.courses.store'), [
            'title' => 'Diseno grafico',
            'available' => '1',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('course_categories', ['title' => 'Diseno grafico']);
    }

    public function test_index_forbidden_without_permission(): void
    {
        $manager = $this->managerWithoutPermissions();

        $this->actingAs($manager)
            ->get(route('manager.categories.courses'))
            ->assertForbidden();
    }

    public function test_create_forbidden_without_permission(): void
    {
        $manager = $this->managerWithoutPermissions();

        $this->actingAs($manager)
            ->get(route('manager.categories.courses.create'))
            ->assertForbidden();
    }

    public function test_edit_forbidden_without_permission(): void
    {
        $manager = $this->managerWithoutPermissions();
        $categorie = CourseCategorie::create([
            'slack' => 'cat-edit',
            'title' => 'Marketing',
            'slug' => 'marketing',
            'available' => 1,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.categories.courses.edit', $categorie->slack))
            ->assertForbidden();
    }

    public function test_view_forbidden_without_permission(): void
    {
        $manager = $this->managerWithoutPermissions();
        $categorie = CourseCategorie::create([
            'slack' => 'cat-view',
            'title' => 'Idiomas',
            'slug' => 'idiomas',
            'available' => 1,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.categories.courses.view', $categorie->slug))
            ->assertForbidden();
    }

    public function test_index_allowed_with_permission(): void
    {
        $manager = $this->managerWithPermissions();

        $this->actingAs($manager)
            ->get(route('manager.categories.courses'))
            ->assertOk();
    }

    public function test_view_shows_title_and_slug_from_real_columns(): void
    {
        $manager = $this->managerWithPermissions();
        $categorie = CourseCategorie::create([
            'slack' => 'cat-detail',
            'title' => 'Finanzas personales',
            'slug' => 'finanzas-personales',
            'available' => 1,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.categories.courses.view', $categorie->slug));

        $response->assertOk();
        $response->assertSeeText('Finanzas personales');
        $response->assertSeeText('finanzas-personales');
    }
}
