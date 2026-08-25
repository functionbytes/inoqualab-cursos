<?php

namespace Tests\Feature\Pages;

use App\Models\Blog\Blog;
use App\Models\Concerns\HasCardImage;
use App\Models\Course\Course;
use Tests\TestCase;

/**
 * Course y Blog registran una conversión 'card' (webp 800px) que pesa entre un
 * tercio y un cuarto del original, pero durante un tiempo ninguna vista la usó:
 * todas pintaban getFullUrl() sin argumento, que devuelve el archivo original.
 * La portada servía así 986 KB de imágenes donde bastaban 352 KB.
 *
 * Se cubre el trait en lugar de las plantillas porque lo que puede volver a
 * romperse es justo lo que decide la URL: si alguien sirve la conversión sin
 * comprobar que existe, cualquier medio cuya conversión no se haya generado
 * queda como imagen rota. Y generarla depende de que cwebp esté instalado,
 * cosa que no se puede dar por hecha en todos los entornos.
 */
class CardImageConversionTest extends TestCase
{
    public function test_course_and_blog_expose_the_helper(): void
    {
        foreach ([Course::class, Blog::class] as $model) {
            $this->assertContains(
                HasCardImage::class,
                class_uses_recursive($model),
                $model.' registra la conversión card pero no usa el trait que la sirve.'
            );
        }
    }

    public function test_uses_the_card_conversion_when_it_exists(): void
    {
        $this->assertSame(
            'https://cdn.test/media/1/conversions/x-card.webp',
            $this->resolveWith($this->fakeMedia(hasConversion: true))
        );
    }

    public function test_falls_back_to_the_original_when_the_conversion_is_missing(): void
    {
        $this->assertSame(
            'https://cdn.test/media/1/x.png',
            $this->resolveWith($this->fakeMedia(hasConversion: false)),
            'Sin conversión generada hay que servir el original: getFullUrl(\'card\') devolvería una URL que no existe.'
        );
    }

    public function test_missing_media_returns_the_given_fallback(): void
    {
        $this->assertSame(
            '/pages/images/blog/default.jpg',
            $this->resolveWith(null, '/pages/images/blog/default.jpg')
        );
    }

    public function test_missing_media_without_fallback_returns_empty_string(): void
    {
        $this->assertSame('', $this->resolveWith(null));
    }

    /** Doble de Media con solo lo que consulta el trait. */
    private function fakeMedia(bool $hasConversion): object
    {
        return new class($hasConversion)
        {
            public function __construct(private bool $hasConversion) {}

            public function hasGeneratedConversion(string $name): bool
            {
                return $this->hasConversion && $name === 'card';
            }

            public function getFullUrl(string $conversion = ''): string
            {
                return $conversion === 'card'
                    ? 'https://cdn.test/media/1/conversions/x-card.webp'
                    : 'https://cdn.test/media/1/x.png';
            }
        };
    }

    /**
     * Ejecuta cardImageUrl() sobre un modelo cuyo getFirstMedia() se sustituye
     * por el doble, sin tocar disco ni base de datos.
     */
    private function resolveWith(?object $media, ?string $fallback = null): string
    {
        $subject = new class($media)
        {
            use HasCardImage;

            public function __construct(private ?object $media) {}

            public function getFirstMedia(string $collection = 'default'): ?object
            {
                return $this->media;
            }
        };

        return $subject->cardImageUrl($fallback);
    }
}
