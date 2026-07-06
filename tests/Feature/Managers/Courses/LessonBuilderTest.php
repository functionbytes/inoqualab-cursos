<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Constructor de clases: reordenamiento drag&drop (renumera 1..N y auto-sana
 * posiciones duplicadas), acciones masivas (publicar/ocultar/eliminar) y que el
 * borrado es soft (preserva el progreso del alumno).
 */
class LessonBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Course $course;

    protected CourseChapter $chapter;

    protected int $typeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->course = Course::factory()->create();
        $this->chapter = CourseChapter::factory()->create(['course_id' => $this->course->id]);
        $this->typeId = DB::table('course_types')->insertGetId([
            'title' => 'Video', 'slug' => 'video-'.Str::random(4),
        ]);
    }

    private function makeLesson(int $position, int $available = 1): CourseLesson
    {
        $lesson = new CourseLesson;
        $lesson->slack = Str::random(10);
        $lesson->title = 'Clase';
        $lesson->available = $available;
        $lesson->position = $position;
        $lesson->type_id = $this->typeId;
        $lesson->course_id = $this->course->id;
        $lesson->chapter_id = $this->chapter->id;
        $lesson->save();

        return $lesson;
    }

    public function test_reorder_renumbers_and_self_heals_duplicate_positions(): void
    {
        // 4 clases todas en position=1 (dato legacy con duplicados).
        $lessons = collect(range(1, 4))->map(fn () => $this->makeLesson(1));
        $reversed = $lessons->pluck('id')->reverse()->values()->all();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.reorder'), ['ids' => $reversed])
            ->assertOk()
            ->assertJsonPath('success', true);

        // Posiciones ahora distintas 1..4 en el nuevo orden.
        foreach ($reversed as $index => $id) {
            $this->assertSame($index + 1, (int) CourseLesson::find($id)->position);
        }
    }

    public function test_bulk_publish_and_hide(): void
    {
        $ids = [$this->makeLesson(1, 0)->id, $this->makeLesson(2, 0)->id];

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.bulk-action'), ['action' => 'publish', 'ids' => $ids])
            ->assertOk();
        $this->assertSame(2, CourseLesson::whereIn('id', $ids)->where('available', 1)->count());

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.bulk-action'), ['action' => 'hide', 'ids' => $ids])
            ->assertOk();
        $this->assertSame(2, CourseLesson::whereIn('id', $ids)->where('available', 0)->count());
    }

    public function test_bulk_delete_is_soft_and_preserves_student_progress(): void
    {
        $lesson = $this->makeLesson(1);

        DB::table('course_progress')->insert([
            'user_id' => User::factory()->create()->id,
            'course_id' => $this->course->id,
            'chapter_id' => $this->chapter->id,
            'lesson_id' => $lesson->id,
            'culminated' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.bulk-action'), ['action' => 'delete', 'ids' => [$lesson->id]])
            ->assertOk();

        // La clase queda soft-deleted (no visible, pero la fila existe).
        $this->assertNull(CourseLesson::find($lesson->id));
        $this->assertNotNull(CourseLesson::withTrashed()->find($lesson->id)->deleted_at);
        // El progreso del alumno se preserva (no cascada FK).
        $this->assertDatabaseHas('course_progress', ['lesson_id' => $lesson->id]);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $id = $this->makeLesson(1)->id;

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.bulk-action'), ['action' => 'destroy_all', 'ids' => [$id]])
            ->assertStatus(422);
    }
}
