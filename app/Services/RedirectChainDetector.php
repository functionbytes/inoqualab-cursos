<?php

namespace App\Services;

use App\Models\Seo\SeoAlert;
use App\Models\Seo\SeoRedirect;
use Illuminate\Support\Collection;

class RedirectChainDetector
{
    private const MAX_DEPTH = 10;

    /**
     * Detect a redirect chain starting from the given source path.
     * Returns the full chain array if a chain longer than 2 hops is found.
     *
     * @param  Collection<string, SeoRedirect>|null  $allRedirects
     * @return array<string>
     */
    public function detect(string $from, ?Collection $allRedirects = null): array
    {
        $allRedirects ??= SeoRedirect::active()->get()->keyBy('source_path');

        $chain = [$from];
        $current = $from;
        $depth = self::MAX_DEPTH;

        while ($depth-- > 0) {
            $redirect = $allRedirects->get($current);

            if (! $redirect) {
                break;
            }

            $current = $redirect->target_path;

            if (in_array($current, $chain, true)) {
                $chain[] = $current;
                break;
            }

            $chain[] = $current;

            if (count($chain) > 2) {
                return $chain;
            }
        }

        return [];
    }

    /**
     * Determina si guardar un redirect `$source -> $target` formaría un bucle:
     * un auto-redirect (`$source === $target`) o un ciclo transitivo que
     * eventualmente vuelve a `$source` siguiendo los redirects activos existentes.
     *
     * Se usa en el guardado (store/update) para RECHAZAR la operación antes de
     * persistirla, a diferencia de detect()/detectAll(), que solo se invocan bajo
     * demanda para aplanar cadenas ya guardadas.
     *
     * @param  int|null  $ignoreId  id del redirect que se está actualizando (se excluye
     *                              del grafo para no comparar contra su propio valor previo)
     */
    public function wouldCreateCycle(string $source, string $target, ?int $ignoreId = null): bool
    {
        if ($source === $target) {
            return true;
        }

        $allRedirects = SeoRedirect::active()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->get()
            ->keyBy('source_path');

        $current = $target;
        $depth = self::MAX_DEPTH;

        while ($depth-- > 0) {
            if ($current === $source) {
                return true;
            }

            $redirect = $allRedirects->get($current);

            if (! $redirect) {
                return false;
            }

            $current = $redirect->target_path;
        }

        // Se agotó la profundidad máxima sin resolver: tratarlo como riesgo de ciclo.
        return true;
    }

    /**
     * Detect all active redirects that participate in a chain.
     *
     * @return Collection<int, array{source: string, chain: array<string>}>
     */
    public function detectAll(): Collection
    {
        $allRedirects = SeoRedirect::active()->get()->keyBy('source_path');

        return $allRedirects
            ->map(function (SeoRedirect $redirect) use ($allRedirects): ?array {
                $chain = $this->detect($redirect->source_path, $allRedirects);

                if (empty($chain)) {
                    return null;
                }

                // Se levanta aquí (no dentro de detect()) porque detectAll()
                // es el barrido completo pensado para reportar/alertar;
                // detect() también se usa para chequeos puntuales bajo
                // demanda donde una alerta sería ruido.
                SeoAlert::raise(
                    SeoAlert::TYPE_REDIRECT_CHAIN,
                    SeoAlert::SEVERITY_WARNING,
                    "Cadena de redirects: {$redirect->source_path}",
                    'Cadena detectada: '.implode(' → ', $chain),
                    $redirect->source_path,
                    ['chain' => $chain]
                );

                return [
                    'source' => $redirect->source_path,
                    'chain' => $chain,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Flatten all detected chains: A→B→C becomes A→C.
     * Returns number of redirects updated.
     */
    public function resolveAll(): int
    {
        $chains = $this->detectAll();
        $updated = 0;

        foreach ($chains as $entry) {
            $source = $entry['source'];
            $chain = $entry['chain'];
            $final = end($chain);

            if ($final === $source) {
                continue;
            }

            $redirect = SeoRedirect::where('source_path', $source)->first();
            if (! $redirect) {
                continue;
            }

            if ($redirect->target_path !== $final) {
                $redirect->update(['target_path' => $final]);
                $updated++;
            }
        }

        return $updated;
    }
}
