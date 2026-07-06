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
 * store()/update() de lecciones envueltos en DB::transaction(). Bloquea la
 * regresión de un bug real encontrado al implementar esto: la validación del
 * enlace de YouTube/Vimeo ocurría DESPUÉS del primer save(), así que una URL
 * inválida dejaba una fila de lección huérfana (sin url/media) en la BD.
 */
class LessonStoreUpdateTransactionTest extends TestCase
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

        // course_types.id es un enum fijo real usado por LessonsController
        // (1=VIDEO con manejo especial de URL; el resto sin mapeo de media no
        // dispara ninguna rama especial). Se inserta con id explícito: el
        // auto-increment de MySQL no se resetea entre tests transaccionales de
        // RefreshDatabase, así que insertGetId() no garantiza id=1.
        DB::table('course_types')->insertOrIgnore([
            ['id' => 1, 'title' => 'VIDEO', 'slug' => 'video'],
            ['id' => 7, 'title' => 'TEXTO', 'slug' => 'texto'],
        ]);
        $this->typeId = 1;
    }

    public function test_store_with_invalid_video_url_does_not_leave_orphan_row(): void
    {
        $before = CourseLesson::count();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.store'), [
                'course' => $this->course->slack,
                'chapter' => $this->chapter->id,
                'title' => 'Clase con URL inválida',
                'type' => '1',
                'platform' => 'youtube',
                'url' => 'https://not-a-video-url.example.com',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', false);

        // No debe quedar ninguna fila creada (antes del fix, sí quedaba una huérfana).
        $this->assertSame($before, CourseLesson::count());
        $this->assertDatabaseMissing('course_lessons', ['title' => 'CLASE CON URL INVÁLIDA']);
    }

    public function test_store_with_valid_video_url_creates_lesson(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.store'), [
                'course' => $this->course->slack,
                'chapter' => $this->chapter->id,
                'title' => 'Clase video valida',
                'type' => '1',
                'position' => 1,
                'platform' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=abc123',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('course_lessons', [
            'title' => 'CLASE VIDEO VALIDA',
            'url' => 'https://www.youtube.com/watch?v=abc123',
            'platform' => 'youtube',
        ]);
    }

    public function test_update_with_invalid_video_url_does_not_change_lesson(): void
    {
        $lesson = new CourseLesson;
        $lesson->slack = Str::random(10);
        $lesson->title = 'ORIGINAL';
        $lesson->available = 1;
        $lesson->position = 1;
        $lesson->type_id = $this->typeId;
        $lesson->course_id = $this->course->id;
        $lesson->chapter_id = $this->chapter->id;
        $lesson->save();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.update'), [
                'slack' => $lesson->slack,
                'chapter' => $this->chapter->id,
                'title' => 'ORIGINAL',
                'type' => '1',
                'platform' => 'vimeo',
                'url' => 'https://not-vimeo.example.com',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', false);

        // El título/tipo original no cambiaron (la transacción no hizo commit parcial).
        $lesson->refresh();
        $this->assertSame('ORIGINAL', $lesson->title);
        $this->assertNull($lesson->url);
    }

    public function test_update_swaps_media_collection_when_type_changes(): void
    {
        $lesson = new CourseLesson;
        $lesson->slack = Str::random(10);
        $lesson->title = 'CLASE';
        $lesson->available = 1;
        $lesson->position = 1;
        $lesson->type_id = $this->typeId;
        $lesson->url = 'https://www.youtube.com/watch?v=old';
        $lesson->platform = 'youtube';
        $lesson->course_id = $this->course->id;
        $lesson->chapter_id = $this->chapter->id;
        $lesson->save();

        // Cambiar de video (type=1) a texto (type=7, sin colección de media conocida).
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.update'), [
                'slack' => $lesson->slack,
                'chapter' => $this->chapter->id,
                'title' => 'CLASE ACTUALIZADA',
                'type' => '7',
                'position' => 1,
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $lesson->refresh();
        $this->assertSame(7, (int) $lesson->type_id);
        $this->assertSame('CLASE ACTUALIZADA', $lesson->title);
    }
}
