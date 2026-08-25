<?php

namespace Tests\Feature\Seo;

use App\Models\Seo\SeoRedirect;
use App\Models\Seo\SeoRedirectHit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SeoRedirectController::analytics() ya leía seo_redirect_hits para pintar el
 * gráfico de los últimos 30 días, pero HandleSeoRedirects solo incrementaba
 * el contador total (hits_count) -- el desglose diario nunca se alimentaba,
 * así que el gráfico siempre mostraba la línea en cero.
 */
class RedirectHitsProducerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_a_redirect_records_a_daily_hit(): void
    {
        $redirect = SeoRedirect::create([
            'source_path' => '/curso-viejo',
            'target_path' => '/curso-nuevo',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/curso-viejo');

        $this->assertDatabaseHas('seo_redirect_hits', [
            'seo_redirect_id' => $redirect->id,
            'hit_date' => now()->toDateString(),
            'hit_count' => 1,
        ]);
        $this->assertSame(1, $redirect->fresh()->hits_count);
    }

    public function test_visiting_the_same_redirect_twice_the_same_day_accumulates_in_one_row(): void
    {
        $redirect = SeoRedirect::create([
            'source_path' => '/curso-viejo',
            'target_path' => '/curso-nuevo',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/curso-viejo');
        $this->get('/curso-viejo');

        $this->assertSame(1, SeoRedirectHit::where('seo_redirect_id', $redirect->id)->count());
        $this->assertSame(2, SeoRedirectHit::where('seo_redirect_id', $redirect->id)->value('hit_count'));
        $this->assertSame(2, $redirect->fresh()->hits_count);
    }
}
