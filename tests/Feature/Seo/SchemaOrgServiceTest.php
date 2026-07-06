<?php

namespace Tests\Feature\Seo;

use App\Services\SchemaOrgService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica la generación de los schemas JSON-LD (schema.org) que alimentan los
 * rich results: Organization, Course/Bundle con offers, FAQPage y BreadcrumbList.
 */
class SchemaOrgServiceTest extends TestCase
{
    use RefreshDatabase;

    private function schema(): SchemaOrgService
    {
        return app(SchemaOrgService::class);
    }

    public function test_bundle_schema_includes_offer_with_price_and_currency(): void
    {
        $bundle = (object) [
            'title' => 'Paquete BPM',
            'description' => '<p>Descripción con <b>HTML</b></p>',
            'price' => 75000,
            'url' => 'https://example.test/bundles/bpm',
        ];

        $result = $this->schema()->bundle($bundle, 'https://example.test/img.jpg');

        $this->assertSame('Course', $result['@type']);
        $this->assertSame('Paquete BPM', $result['name']);
        $this->assertArrayHasKey('offers', $result);
        $this->assertSame('75000', $result['offers']['price']);
        $this->assertSame('COP', $result['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $result['offers']['availability']);
        // La descripción no debe contener HTML
        $this->assertStringNotContainsString('<', $result['description']);
    }

    public function test_bundle_without_price_has_no_offer(): void
    {
        $bundle = (object) ['title' => 'Gratis', 'description' => 'x', 'price' => 0, 'url' => 'https://example.test/b'];

        $result = $this->schema()->bundle($bundle);

        $this->assertArrayNotHasKey('offers', $result);
    }

    public function test_faq_schema_generates_faqpage_with_questions(): void
    {
        $result = $this->schema()->faq([
            ['question' => '¿Vigencia?', 'answer' => 'Un año.'],
            ['question' => '¿Certificado?', 'answer' => 'Sí.'],
        ]);

        $this->assertSame('FAQPage', $result['@type']);
        $this->assertCount(2, $result['mainEntity']);
        $this->assertSame('Question', $result['mainEntity'][0]['@type']);
        $this->assertSame('¿Vigencia?', $result['mainEntity'][0]['name']);
        $this->assertSame('Un año.', $result['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_breadcrumbs_schema_has_sequential_positions(): void
    {
        $result = $this->schema()->breadcrumbs([
            ['name' => 'Inicio', 'url' => 'https://example.test/'],
            ['name' => 'Cursos', 'url' => 'https://example.test/courses'],
            ['name' => 'Curso X', 'url' => 'https://example.test/courses/x'],
        ]);

        $this->assertSame('BreadcrumbList', $result['@type']);
        $this->assertCount(3, $result['itemListElement']);
        $this->assertSame(1, $result['itemListElement'][0]['position']);
        $this->assertSame(3, $result['itemListElement'][2]['position']);
        $this->assertSame('Curso X', $result['itemListElement'][2]['name']);
    }
}
