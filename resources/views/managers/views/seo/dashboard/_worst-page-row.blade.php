{{--
    Fila de "Páginas con peor score" (dashboard SEO). Ícono de editar como SVG
    inline en vez de <i class="fas fa-pen"> -- ese ícono no se pintaba dentro
    del botón btn-sm btn-light (quedaba un círculo gris vacío), reportado por
    el usuario el 2026-09-23.
--}}
@php
    $sc = $page->seo_score;
    $scoreBg = match (true) {
        $sc >= 90 => 'seo-score--a',
        $sc >= 75 => 'seo-score--b',
        $sc >= 60 => 'seo-score--c',
        $sc >= 40 => 'seo-score--d',
        default => 'seo-score--f',
    };
    $typeLabel = match (true) {
        str_contains($page->seoable_type ?? '', 'Course') => 'Curso',
        str_contains($page->seoable_type ?? '', 'Blog') => 'Blog',
        str_contains($page->seoable_type ?? '', 'Bundle') => 'Bundle',
        default => class_basename($page->seoable_type ?? ''),
    };
@endphp
<div class="d-flex align-items-center gap-2 seo-worst-row {{ $critical ? 'seo-worst-row--critical' : '' }}">
    <span class="badge {{ $scoreBg }} flex-shrink-0 seo-score-badge">{{ $sc }}</span>
    <div class="flex-grow-1 text-truncate">
        <span class="small fw-semibold text-truncate d-block">
            {{ $page->title ?: ($typeLabel . ' #' . $page->seoable_id) }}
        </span>
    </div>
    <a href="{{ route('manager.seo.metas.edit', $page->id) }}"
       class="seo-worst-row-edit flex-shrink-0" title="Editar" aria-label="Editar {{ $page->title ?: $typeLabel }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 20h9"></path>
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
        </svg>
    </a>
</div>
