<?php

namespace Tests\Unit\Models;

use App\Models\Bundle\Bundle;
use App\Models\Seo\SeoMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: isFollowable() (duplicado en SeoMeta::isFollowable() y en el
 * trait HasSeo::isFollowable()) hacía `str_contains($robots, 'follow')`. La
 * cadena "noindex,nofollow" CONTIENE la subcadena "follow", así que el
 * método devolvía true justo cuando el editor pedía explícitamente lo
 * contrario ("no seguir este enlace").
 */
class SeoIsFollowableTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_meta_is_not_followable_when_robots_says_nofollow(): void
    {
        $meta = new SeoMeta(['robots' => 'noindex,nofollow']);

        $this->assertFalse($meta->isFollowable());
    }

    public function test_seo_meta_is_followable_when_robots_says_follow(): void
    {
        $meta = new SeoMeta(['robots' => 'index,follow']);

        $this->assertTrue($meta->isFollowable());
    }

    private function makeBundle(): Bundle
    {
        return Bundle::create([
            'slack' => 'bundle-'.uniqid(),
            'title' => 'Paquete de prueba',
            'slug' => 'paquete-'.uniqid(),
            'price' => 100000,
            'available' => 1,
        ]);
    }

    public function test_has_seo_trait_is_not_followable_when_robots_says_nofollow(): void
    {
        $bundle = $this->makeBundle();
        $bundle->setRelation('seoMeta', new SeoMeta(['robots' => 'noindex,nofollow']));

        $this->assertFalse($bundle->isFollowable());
    }

    public function test_has_seo_trait_defaults_to_followable_without_seo_meta(): void
    {
        $bundle = $this->makeBundle();
        $bundle->setRelation('seoMeta', null);

        $this->assertTrue($bundle->isFollowable());
    }
}
