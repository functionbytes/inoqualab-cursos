<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoPagespeedSnapshot;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * SeoAuditController::coreWebVitals() ya consultaba la API real de PageSpeed
 * Insights y devolvía el resultado al navegador, pero nunca lo guardaba --
 * seo_pagespeed_snapshots existía y no tenía ni modelo. Tampoco había ningún
 * botón en el panel que llamara al endpoint: quedaba muerto en los dos lados.
 */
class PagespeedSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function fakePageSpeedResponse(): array
    {
        return [
            'lighthouseResult' => [
                'categories' => [
                    'performance' => ['score' => 0.87],
                    'seo' => ['score' => 0.95],
                    'accessibility' => ['score' => 0.90],
                    'best-practices' => ['score' => 0.83],
                ],
                'audits' => [
                    'largest-contentful-paint' => ['numericValue' => 2450.3, 'displayValue' => '2.5 s'],
                    'total-blocking-time' => ['numericValue' => 120.0, 'displayValue' => '120 ms'],
                    'cumulative-layout-shift' => ['numericValue' => 0.08, 'displayValue' => '0.08'],
                    'first-contentful-paint' => ['numericValue' => 1100.0, 'displayValue' => '1.1 s'],
                    'server-response-time' => ['numericValue' => 300.0, 'displayValue' => '300 ms'],
                ],
            ],
        ];
    }

    public function test_audit_index_renders_the_pagespeed_card(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.seo.audit.index'))
            ->assertOk()
            ->assertSee('PageSpeed Insights')
            ->assertSee(route('manager.seo.audit.core-web-vitals'), false);
    }

    public function test_core_web_vitals_persists_a_snapshot(): void
    {
        Http::fake([
            'pagespeedonline/v5/runPagespeed*' => Http::response($this->fakePageSpeedResponse(), 200),
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.core-web-vitals'), [
                'url' => 'https://training.test/curso/algo',
                'strategy' => 'mobile',
            ])
            ->assertOk()
            ->assertJson([
                'performance_score' => 87,
                'seo_score' => 95,
                'accessibility_score' => 90,
                'best_practices_score' => 83,
                'strategy' => 'mobile',
            ]);

        $this->assertDatabaseHas('seo_pagespeed_snapshots', [
            'url' => 'https://training.test/curso/algo',
            'url_path' => '/curso/algo',
            'strategy' => 'mobile',
            'performance' => 87,
            'seo' => 95,
        ]);

        $snapshot = SeoPagespeedSnapshot::first();
        $this->assertSame(2450.3, $snapshot->lcp_ms);
        $this->assertSame(0.08, $snapshot->cls);
        $this->assertSame(300.0, $snapshot->ttfb_ms);
        // Sin datos de campo (CrUX) para INP en la respuesta simulada -- debe
        // quedar null, no un valor inventado.
        $this->assertNull($snapshot->inp_ms);
    }

    public function test_core_web_vitals_does_not_persist_when_the_api_call_fails(): void
    {
        Http::fake([
            'pagespeedonline/v5/runPagespeed*' => Http::response([], 500),
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.core-web-vitals'), [
                'url' => 'https://training.test/curso/algo',
            ])
            ->assertStatus(422);

        $this->assertSame(0, SeoPagespeedSnapshot::count());
    }
}
