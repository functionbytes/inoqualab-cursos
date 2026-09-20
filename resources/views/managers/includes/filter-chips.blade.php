{{--
    Chips de filtros activos con botón X individual para eliminar cada uno.

    Uso:
        @include('managers.includes.filter-chips', ['chips' => $filterChips])

    $filterChips = [
        ['label' => 'Estado: Suscritos', 'clear_url' => route('...', [...])],
        ['label' => 'Origen: Formulario', 'clear_url' => route('...', [...])],
    ];

    Construir $filterChips en @php del padre conservando los demás filtros en cada clear_url.
--}}
@if(!empty($chips ?? []))
<hr class="my-2 opacity-25">
<div class="d-flex flex-wrap align-items-center gap-2">
    <span class="text-muted filter-chips-label">Filtros:</span>
    @foreach($chips as $chip)
        <a href="{{ $chip['clear_url'] }}"
           class="d-inline-flex align-items-center gap-1 text-decoration-none filter-chip">
            {{ $chip['label'] }}
            <i class="fas fa-xmark filter-chip-icon"></i>
        </a>
    @endforeach
</div>
@endif

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/filter-chips.css') }}">
@endpush
