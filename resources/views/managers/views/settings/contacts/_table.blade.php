{{--
    Partial AJAX: filtros + tabla + paginacion de contactos.

    Se incluye normalmente desde index.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($reviewed ?? '') !== '') {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($reviewed === '1' ? 'Gestionado' : 'Pendiente'),
                    'clear_url' => route('manager.contacts', array_filter(['search' => $searchKey ?? ''])),
                ];
            }
        @endphp
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
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($contacts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="contacts-col-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Nombre</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $contact)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $contact->id }}">
                                </td>
                                <td class="fw-semibold">
                                    {{ Str::words(Str::title(Str::lower($contact->firstname . ' ' . $contact->lastname)), 12, '...') }}
                                </td>
                                <td class="text-center">
                                    @if($contact->reviewed)
                                        <span class="badge bg-success-subtle text-success">Gestionado</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ date('d/m/Y', strtotime($contact->updated_at)) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('contacts.update')
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('manager.contacts.edit', $contact->slack) }}">
                                                    Editar
                                                </a>
                                            </li>
                                            @endcan
                                            @can('contacts.delete')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item btn-delete" href="#"
                                                   data-url="{{ route('manager.contacts.destroy', $contact->slack) }}"
                                                   data-title="Eliminar: {{ Str::title(Str::lower($contact->firstname . ' ' . $contact->lastname)) }}">
                                                    Eliminar
                                                </a>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-envelope fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') !== '' || ($reviewed ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay contactos
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') !== '' || ($reviewed ?? '') !== '')
                        No hay contactos que coincidan con los filtros aplicados.
                    @else
                        Los mensajes de contacto aparecerán aquí cuando alguien complete el formulario.
                    @endif
                </p>
                @if(($searchKey ?? '') !== '' || ($reviewed ?? '') !== '')
                    <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $contacts,
        'itemLabel' => 'contactos',
    ])

</div>
