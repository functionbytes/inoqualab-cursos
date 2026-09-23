<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseAnnouncement;
use App\Models\Course\CourseChapter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: ChapterController y AnnouncementsController::store()/update()
 * usaban `Request $request` plano sin ningún Form Request -- un POST sin
 * `title` (o sin `available` en Chapters, NOT NULL en BD) pasaba directo al
 * INSERT/UPDATE y reventaba con un 500 (Integrity constraint violation) en
 * vez de un 422 legible. Mismo patrón ya visto y corregido antes en
 * Testimonies/Sliders/Instructions.
 */
class ChapterAnnouncementValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->course = Course::factory()->create();
    }

    public function test_chapter_store_rejects_empty_title(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.chapters.store'), [
                'course' => $this->course->slack,
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_chapter_store_creates_with_valid_data(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.chapters.store'), [
                'course' => $this->course->slack,
                'title' => 'Tema de prueba',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('course_chapters', [
            'course_id' => $this->course->id,
            'title' => 'TEMA DE PRUEBA',
        ]);
    }

    public function test_chapter_update_rejects_missing_available(): void
    {
        $chapter = CourseChapter::create([
            'slack' => (string) Str::uuid(),
            'course_id' => $this->course->id,
            'title' => 'ORIGINAL',
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.chapters.update'), [
                'slack' => $chapter->slack,
                'title' => 'Actualizado',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('available');
    }

    public function test_announcement_store_rejects_empty_title(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.announcements.store'), [
                'course' => $this->course->slack,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_announcement_store_creates_with_valid_data(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.announcements.store'), [
                'course' => $this->course->slack,
                'title' => 'Anuncio de prueba',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('course_announcements', [
            'course_id' => $this->course->id,
            'title' => 'Anuncio de prueba',
        ]);
    }

    public function test_announcement_update_rejects_empty_title(): void
    {
        $announcement = CourseAnnouncement::create([
            'slack' => (string) Str::uuid(),
            'course_id' => $this->course->id,
            'title' => 'ORIGINAL',
            'user_id' => $this->manager->id,
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.announcements.update'), [
                'slack' => $announcement->slack,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }
}
