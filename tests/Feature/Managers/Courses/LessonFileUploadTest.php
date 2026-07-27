<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * El adjunto de una lección se guarda en el disco 'media', que apunta a
 * public_path('media') — es decir, dentro del webroot. Antes la validación era
 * ['nullable','file','max:512000'] sin `mimes:` y las colecciones de Spatie no
 * declaraban acceptsMimeTypes(), así que un manager podía dejar un .php ahí.
 *
 * Estos tests fijan que cada tipo de lección solo acepte sus formatos.
 */
class LessonFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Course $course;

    protected CourseChapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        // El disco 'media' real es public_path('media'): sin fake, el caso feliz
        // dejaría el PNG dentro del repo (RefreshDatabase revierte la BD, no el disco).
        Storage::fake('media');

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->course = Course::factory()->create();
        $this->chapter = CourseChapter::factory()->create(['course_id' => $this->course->id]);

        // ids reales de course_types usados por LessonsController::$mediaCollections.
        DB::table('course_types')->insertOrIgnore([
            ['id' => 3, 'title' => 'IMAGEN', 'slug' => 'imagen'],
            ['id' => 5, 'title' => 'PDF', 'slug' => 'pdf'],
        ]);
    }

    private function storeLesson(UploadedFile $file, string $type): TestResponse
    {
        return $this->actingAs($this->manager)
            ->postJson(route('manager.courses.lessons.store'), [
                'course' => $this->course->slack,
                'chapter' => $this->chapter->id,
                'title' => 'Clase con adjunto',
                'type' => $type,
                'position' => 1,
                'available' => 1,
                'file' => $file,
            ]);
    }

    public function test_php_file_disguised_as_pdf_is_rejected(): void
    {
        $payload = UploadedFile::fake()->createWithContent(
            'shell.php',
            '<?php echo shell_exec($_GET["c"]); ?>'
        );

        $this->storeLesson($payload, '5')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseMissing('course_lessons', ['title' => 'CLASE CON ADJUNTO']);
    }

    public function test_php_file_with_pdf_extension_is_rejected_by_mime_sniffing(): void
    {
        // `mimes:` valida por el tipo real detectado con finfo, no por el nombre:
        // renombrar el shell a .pdf no basta para colarlo.
        //
        // Aquí NO sirve UploadedFile::fake(): el helper deriva getMimeType() de
        // la extensión, así que devolvería 'application/pdf' y el test pasaría
        // por la razón equivocada. Hace falta un archivo real en disco.
        $path = tempnam(sys_get_temp_dir(), 'lesson_probe_').'.pdf';
        file_put_contents($path, '<?php echo shell_exec($_GET["c"]); ?>');
        $payload = new UploadedFile($path, 'payload.pdf', null, null, true);

        $this->assertSame('text/x-php', $payload->getMimeType());

        $this->storeLesson($payload, '5')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseMissing('course_lessons', ['title' => 'CLASE CON ADJUNTO']);

        @unlink($path);
    }

    public function test_image_type_rejects_a_zip(): void
    {
        $payload = UploadedFile::fake()->createWithContent('bundle.zip', 'PK'.str_repeat('x', 100));

        $this->storeLesson($payload, '3')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_image_type_accepts_a_png(): void
    {
        $payload = UploadedFile::fake()->image('portada.png', 40, 40);

        $this->storeLesson($payload, '3')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('course_lessons', ['title' => 'CLASE CON ADJUNTO']);
    }
}
