{{--
    Footer de paginacion estandar para listados del panel ("Mostrando X-Y de Z ...").
    Siempre visible (con o sin paginas), incluye selector de items por pagina.

    Uso en la vista index, como ultimo elemento dentro de <div class="card">,
    despues del card-body de la tabla:

        @include('managers.includes.pagination-footer', [
            'paginator' => $contacts,
            'itemLabel' => 'contactos',
        ])

    El selector "items por pagina" solo tiene efecto si el controller pagina
    con paginationNumber() (respeta ?per_page= automaticamente) o con
    paginationNumber($n) para vistas que ya pedian un tamaño propio — ver
    app/helpers.php. Se envia vía AjaxTable (managers/js/ajax-table.js), sin
    recargar la pagina, igual que la busqueda/filtro/paginacion.
--}}
@php
    $perPageOptions = [10, 20, 50, 100, 200];
    $currentPerPage = (int) request('per_page', $paginator->perPage());
    // El default de config('settings.pagination') puede no ser uno de los
    // valores estandar (ej. 15): si no esta en la lista, se agrega para que
    // el select siempre refleje el tamaño real de pagina, nunca "miente".
    if (! in_array($currentPerPage, $perPageOptions, true)) {
        $perPageOptions[] = $currentPerPage;
        sort($perPageOptions);
    }
@endphp
<div class="card-footer bg-white border-top pagination-footer">
    <div class="pagination-footer-info">
        <span class="pagination-footer-count">
            Mostrando {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} {{ $itemLabel }}
        </span>
        <div class="pagination-footer-per-page">
            <span>Mostrar</span>
            <select class="ajax-per-page-select" aria-label="Items por página">
                @foreach($perPageOptions as $opt)
                    <option value="{{ $opt }}" {{ $currentPerPage === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @if($paginator->hasPages())
        {{ $paginator->appends(request()->input())->links() }}
    @endif
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/pagination-footer.css') }}">
@endpush
