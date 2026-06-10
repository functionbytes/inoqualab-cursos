<?php

namespace App\Models\Concerns;

/**
 * Métodos estáticos para buscar por slack/slug/id sin el antipatrón del Builder.
 *
 * Los scopes scopeSlack/scopeSlug/scopeId terminan en ->first(), que devuelve
 * null cuando no hay match. Laravel en callScope ejecuta `$result ?? $this`,
 * convirtiendo ese null en el Builder. Por eso Model::slack('x') cuando no
 * existe devuelve un Builder (truthy) en lugar de null.
 *
 * Estos métodos usan firstWhere() directamente y devuelven ?static limpiamente.
 */
trait HasFinders
{
    public static function findBySlack(string $slack): ?static
    {
        return static::firstWhere('slack', $slack);
    }

    public static function findBySlug(string $slug): ?static
    {
        return static::firstWhere('slug', $slug);
    }

    public static function findByIdentifier(string $value): ?static
    {
        return static::firstWhere('identification', $value);
    }
}
