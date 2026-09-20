<?php

namespace Tests\Feature\Customers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cubre el patrón AJAX de Documentos: solo documentos disponibles, búsqueda,
 * filtro por tipo de archivo (derivado de la extensión del adjunto, no de una
 * columna) y paginación.
 */
class DocumentsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
        $this->customer = User::factory()->customer()->create();
    }

    private function documentWithFile(string $filename, array $attributes = []): Document
    {
        $document = Document::factory()->create($attributes);
        $document->addMedia(UploadedFile::fake()->create($filename, 10))
            ->toMediaCollection('files');

        return $document->fresh();
    }

    public function test_index_returns_the_full_view_for_a_normal_request(): void
    {
        Document::factory()->create();

        $this->actingAs($this->customer)
            ->get(route('customers.documents'))
            ->assertOk()
            ->assertViewIs('customers.views.documents.index');
    }

    public function test_index_returns_json_with_rendered_html_for_an_ajax_request(): void
    {
        Document::factory()->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.documents'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html', 'total', 'label']);

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('documento', $response->json('label'));
    }

    public function test_index_only_shows_available_documents(): void
    {
        $visible = Document::factory()->create();
        Document::factory()->hidden()->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.documents'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        // La vista muestra el título en mayúsculas (Str::upper(Str::lower(...))).
        $this->assertStringContainsString(Str::upper($visible->title), $response->json('html'));
    }

    public function test_index_search_filters_by_title(): void
    {
        Document::factory()->create(['title' => 'Reglamento interno de laboratorio']);
        Document::factory()->create(['title' => 'Formato de inscripción']);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.documents', ['search' => 'reglamento']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
    }

    public function test_index_filters_by_pdf_type(): void
    {
        $pdf = $this->documentWithFile('manual.pdf', ['title' => 'Manual PDF']);
        $this->documentWithFile('foto.png', ['title' => 'Foto']);

        $response = $this->actingAs($this->customer)
            ->get(route('customers.documents', ['type' => 'pdf']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString(Str::upper($pdf->title), $response->json('html'));
    }

    public function test_index_paginates_results(): void
    {
        Document::factory()->count(16)->create();

        $response = $this->actingAs($this->customer)
            ->get(route('customers.documents', ['page' => 2]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertStringContainsString('Mostrando 16-16 de 16 resultados', $response->json('html'));
    }
}
