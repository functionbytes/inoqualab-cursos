<?php

namespace App\Services\IncomingMail;

use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseAlias;
use Illuminate\Support\Str;

class EnterpriseMatcher
{
    /**
     * Attempt to match an Enterprise by code and/or name.
     *
     * Strategy (first match wins):
     * 1. Direct code lookup via Enterprise::byCode()
     * 2. Alias lookup (alias_type='code', normalized_value = normalize(code))
     * 3. Alias lookup (alias_type='name', normalized_value = normalize(name))
     * 4. Fuzzy LIKE on enterprises.title (normalized)
     */
    public function match(?string $code, ?string $name): ?Enterprise
    {
        if ($code !== null && $code !== '') {
            $enterprise = Enterprise::byCode($code);

            if ($enterprise !== null) {
                return $enterprise;
            }

            $enterprise = $this->findByAlias(EnterpriseAlias::TYPE_CODE, $this->normalize($code));

            if ($enterprise !== null) {
                return $enterprise;
            }
        }

        if ($name !== null && $name !== '') {
            $enterprise = $this->findByAlias(EnterpriseAlias::TYPE_NAME, $this->normalize($name));

            if ($enterprise !== null) {
                return $enterprise;
            }

            $enterprise = $this->findByTitle($name);

            if ($enterprise !== null) {
                return $enterprise;
            }
        }

        return null;
    }

    public function normalize(string $value): string
    {
        // Transliterate accented characters, lowercase, collapse whitespace
        return trim(preg_replace('/\s+/', ' ', strtolower(Str::ascii($value))));
    }

    private function findByAlias(string $type, string $normalizedValue): ?Enterprise
    {
        $alias = EnterpriseAlias::query()
            ->where('alias_type', $type)
            ->where('normalized_value', $normalizedValue)
            ->with('enterprise')
            ->first();

        return $alias?->enterprise;
    }

    private function findByTitle(string $name): ?Enterprise
    {
        $normalized = $this->normalize($name);

        // Use a limited LIKE search on the stored title to keep the query light
        return Enterprise::query()
            ->whereRaw('LOWER(title) LIKE ?', ['%'.str_replace(['%', '_'], ['\%', '\_'], $normalized).'%'])
            ->first();
    }
}
