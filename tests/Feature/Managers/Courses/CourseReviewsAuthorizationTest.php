<?php

namespace Tests\Feature\Managers\Courses;

/**
 * Regresión del hallazgo #2 de la auditoría: `ReviewsController::index` no
 * verificaba ningún permiso Spatie (solo `destroy` lo hacía).
 */
class CourseReviewsAuthorizationTest extends CategoriesTestCase
{
    public function test_index_forbidden_without_permission(): void
    {
        $manager = $this->managerWithoutPermissions();

        $this->actingAs($manager)
            ->get(route('manager.reviews'))
            ->assertForbidden();
    }

    public function test_index_allowed_with_permission(): void
    {
        $manager = $this->managerWithPermissions();

        $this->actingAs($manager)
            ->get(route('manager.reviews'))
            ->assertOk();
    }
}
