<?php

namespace App\Models\Concerns;

/**
 * Resuelve la URL de la miniatura para las tarjetas del frontend público.
 *
 * Course y Blog registran una conversión 'card' (webp, 800px, calidad 82) que
 * pesa entre un tercio y un cuarto del PNG original, pero las vistas pintaban
 * `getFirstMedia('thumbnail')->getFullUrl()`, que devuelve SIEMPRE el original:
 * la conversión se generaba y no la usaba nadie. Solo la portada bajaba así
 * casi un mega de imágenes.
 *
 * El fallback no es decorativo: `getFullUrl('card')` construye la URL de la
 * conversión aunque el fichero no exista, así que servir la conversión a ciegas
 * dejaría la imagen rota en cuanto una no se genere. Y puede no generarse: la
 * conversión es `->optimize()` sobre webp, que depende de que cwebp esté
 * instalado en la máquina. Hoy todos los Course con thumbnail la tienen, pero
 * los 11 blogs no tienen imagen ninguna, así que la rama de Blog no se ha
 * ejercitado nunca con datos reales.
 */
trait HasCardImage
{
    public function cardImageUrl(?string $fallback = null): string
    {
        $media = $this->getFirstMedia('thumbnail');

        if ($media === null) {
            return $fallback ?? '';
        }

        return $media->hasGeneratedConversion('card')
            ? $media->getFullUrl('card')
            : $media->getFullUrl();
    }
}
