{{--
    Barra de busqueda + filtros: buscador, boton "+ Filtro" que abre un
    popover anclado al boton (no modal) con los campos de filtro, y chips
    de los filtros actualmente activos. Usar junto con
    public/managers/js/filter-toolbar.js.

    Va DENTRO de un <form method="GET" id="searchForm"> ya abierto por la
    vista, junto con sus <input type="hidden"> de filtro (uno por campo,
    los actualiza FilterToolbar al pulsar "Aplicar").

    Uso en la vista index:

        <form method="GET" action="{{ Request::url() }}" id="searchForm">
            <input type="hidden" name="reviewed" id="filterReviewed" value="{{ $reviewed ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_reviewed" value="" {{ ($reviewed ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_reviewed" value="1" {{ ($reviewed ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Gestionado</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_reviewed" value="0" {{ ($reviewed ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Pendiente</span>
                    </label>
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por nombre...',
                'popoverBody' => $popoverBody,    // omitir si la tabla no tiene filtros
                'filterChips' => $filterChips ?? [],
            ])
        </form>

    y en @push('scripts'):

        FilterToolbar.init({
            fields: { filterReviewed: 'popover_reviewed' },  // { hiddenInputId: nombre del grupo radio/checkbox }
        });

    `$filterChips`: array de ['label' => 'Estado: Gestionado', 'clear_url' => route(...)]
    con un elemento por filtro activo (para mostrar como chip removible).
--}}
<div class="d-flex gap-2 align-items-center filter-toolbar-bar">
    <div class="flex-fill">
        <div class="input-group">
            <span class="input-group-text text-muted">
                {!! \App\Html\IconHelper::render('search') !!}
            </span>
            <input type="search" name="{{ $searchName ?? 'search' }}" class="form-control border-start-0"
                   placeholder="{{ $searchPlaceholder ?? 'Buscar...' }}"
                   value="{{ $searchValue ?? '' }}">
        </div>
    </div>

    @if(isset($popoverBody))
        {{-- Patron nuevo: boton icon-only con popover anclado + chips --}}
        <div class="filter-popover-wrap">
            <button type="button" id="filters-trigger" class="filter-trigger-btn {{ !empty($filterChips) ? 'has-active' : '' }}"
                    title="Filtros" aria-label="Filtros" aria-haspopup="true" aria-expanded="false">
                {!! \App\Html\IconHelper::render('sliders', 17) !!}
                @if(!empty($filterChips))
                    <span class="filter-trigger-badge">{{ count($filterChips) }}</span>
                @endif
            </button>

            <div id="filters-popover" class="filter-popover" role="dialog" aria-label="Filtros">
                <div class="filter-popover-title">Filtrar por</div>
                {!! $popoverBody !!}
                <div class="filter-popover-footer">
                    <a href="{{ Request::url() }}" class="filter-popover-clear">Limpiar</a>
                    <button type="button" id="applyFiltersBtn" class="filter-popover-apply">Aplicar</button>
                </div>
            </div>
        </div>
    @elseif(isset($filtersModalTarget))
        {{-- Patron legacy: boton icon-only que abre un modal Bootstrap (vistas no migradas aun) --}}
        <button type="button" class="btn btn-outline-secondary btn-icon flex-shrink-0" title="Filtros"
                data-bs-toggle="modal" data-bs-target="{{ $filtersModalTarget }}">
            {!! \App\Html\IconHelper::render('sliders') !!}
            @if(($activeFilters ?? 0) > 0)
                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
            @endif
        </button>
    @endif

    <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Buscar</button>
</div>

@if(!empty($filterChips))
    <div class="filter-chips">
        @foreach($filterChips as $chip)
            <a href="{{ $chip['clear_url'] }}" class="filter-chip">
                {{ $chip['label'] }}
                {!! \App\Html\IconHelper::render('x', 12) !!}
            </a>
        @endforeach
        <a href="{{ Request::url() }}" class="filter-chip-clear-all">Limpiar todo</a>
    </div>
@endif

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/filter-toolbar.css') }}?v={{ @filemtime(public_path('managers/css/includes/filter-toolbar.css')) ?: 1 }}">
@endpush
