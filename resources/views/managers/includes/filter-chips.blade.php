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
    <span class="text-muted" style="font-size:.8rem;">Filtros:</span>
    @foreach($chips as $chip)
        <a href="{{ $chip['clear_url'] }}"
           class="d-inline-flex align-items-center gap-1 text-decoration-none"
           style="border-radius:5px; font-size:.8rem; line-height:1.4; background:rgba(10,37,64,.39); border:1.5px solid rgba(10,37,64,.23); color:#071a28; padding:.25rem .5rem;">
            {{ $chip['label'] }}
            <i class="fas fa-xmark" style="font-size:.65rem; opacity:.7;"></i>
        </a>
    @endforeach
</div>
@endif
