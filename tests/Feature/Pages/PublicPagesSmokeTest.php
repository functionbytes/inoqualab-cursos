<?php

namespace Tests\Feature\Pages;

use App\Models\Blog\Blog;
use App\Models\Course\Course;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Barrido de las páginas públicas: cada ruta GET sin parámetros debe responder
 * 200 con datos en la BD.
 *
 * Existe porque el frontend público no tenía cobertura y por ahí se coló un
 * fallo de compilación Blade en los parciales del blog que tumbaba /blogs y
 * /blogs/{slug} en cualquier visita. Un smoke test lo habría cazado al instante.
 *
 * No sustituye a los tests de comportamiento (checkout, carrito y cupones tienen
 * los suyos en tests/Feature/Checkout y tests/Unit): aquí solo se comprueba que
 * la plantilla compila y el controller responde.
 */
class PublicPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** Rutas GET públicas sin parámetros que deben devolver 200. */
    public static function publicRoutes(): array
    {
        return [
            'home' => ['index'],
            'about' => ['about'],
            'blogs' => ['blogs'],
            'bundles' => ['bundles'],
            'carrito' => ['cart.index'],
            'drawer del carrito' => ['cart.drawer'],
            'certificadores' => ['certifiers'],
            'contacto' => ['contacts'],
            'cursos' => ['courses'],
            'faqs' => ['faqs'],
            'instructivos' => ['instructions'],
            'términos' => ['terms'],
            'políticas' => ['politics'],
            'comercial' => ['commercials'],
            'robots.txt' => ['robots.txt'],
            'llms.txt' => ['llms.txt'],
            'sitemap' => ['sitemap.index'],
            'sitemap de cursos' => ['sitemap.courses'],
            'sitemap de blogs' => ['sitemap.blogs'],
            'sitemap principal' => ['sitemap.main'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Contenido mínimo para que los listados no rendericen solo el estado vacío.
        Course::factory()->count(3)->create();
        Blog::factory()->count(3)->create();
    }

    #[DataProvider('publicRoutes')]
    public function test_public_route_responds_ok(string $routeName): void
    {
        $this->get(route($routeName))->assertOk();
    }

    public function test_course_detail_renders(): void
    {
        $course = Course::factory()->create();

        // Ojo: el parámetro de ruta se llama {slug} pero CoursesController::view()
        // resuelve por `slack` — es lo que pasan los enlaces internos.
        $this->get(route('courses.view', $course->slack))->assertOk();
    }

    public function test_unknown_course_returns_404(): void
    {
        $this->get(route('courses.view', 'no-existe'))->assertNotFound();
    }

    /** `short` no está en $fillable, se asigna suelto igual que en el controller. */
    private function makeInstruction(string $title, string $slug): Instruction
    {
        $categorie = InstructionCategorie::create([
            'slack' => Str::random(10),
            'title' => 'General',
            'slug' => 'general-'.Str::random(5),
            'available' => 1,
        ]);

        $instruction = new Instruction;
        $instruction->slack = Str::random(10);
        $instruction->title = $title;
        $instruction->slug = $slug;
        $instruction->short = '<p>Resumen del instructivo.</p>';
        $instruction->description = '<p>Pasos detallados del instructivo.</p>';
        $instruction->available = 1;
        $instruction->category_id = $categorie->id;
        $instruction->save();

        return $instruction;
    }

    public function test_instructions_index_renders(): void
    {
        $this->makeInstruction('Cómo matricularse', 'como-matricularse');

        $this->get(route('instructions'))
            ->assertOk()
            ->assertSee('Cómo matricularse', false)
            ->assertSee('Resumen del instructivo', false);
    }

    public function test_instruction_detail_renders(): void
    {
        $instruction = $this->makeInstruction('Cómo descargar tu certificado', 'como-descargar-certificado');

        $this->get(route('instructions.view', $instruction->slug))
            ->assertOk()
            ->assertSee('Pasos detallados del instructivo', false);
    }

    public function test_instruction_detail_also_resolves_by_slack(): void
    {
        // Los enlaces del portal de clientes apuntan por slack, no por slug.
        $instruction = $this->makeInstruction('Guía de acceso', 'guia-de-acceso');

        $this->get(route('instructions.view', $instruction->slack))->assertOk();
    }

    public function test_unpublished_instruction_returns_404(): void
    {
        $instruction = $this->makeInstruction('Borrador interno', 'borrador-interno');
        $instruction->available = 0;
        $instruction->save();

        $this->get(route('instructions.view', $instruction->slug))->assertNotFound();
    }
}
