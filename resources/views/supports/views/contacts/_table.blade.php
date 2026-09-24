<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Solicitudes recibidas</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Gestionados</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['reviewed']) }}</h4>
                        <span class="text-muted">Ya atendidos</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Pendientes</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                        <span class="text-muted">Por gestionar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($reviewed ?? '') !== '') {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($reviewed === '1' ? 'Gestionado' : 'Pendiente'),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('reviewed')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.contacts') }}" id="searchForm">

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
                            <th class="col-checkbox">
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
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($contact->firstname.' '.$contact->lastname)), 12, '...') }}</div>
                                </td>
                                <td class="text-center">
                                    @if($contact->reviewed == 1)
                                        <span class="badge bg-success-subtle text-success">Gestionado</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $contact->updated_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.contacts.view', $contact->slack) }}">
                                                    Visualizar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.contacts.edit', $contact->slack) }}">
                                                    Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item confirm-delete" href="#"
                                                   data-href="{{ route('support.contacts.destroy', $contact->slack) }}">
                                                    Eliminar
                                                </a>
                                            </li>
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-contacts', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if($searchKey || ($reviewed ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay contactos
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if($searchKey || ($reviewed ?? '') !== '')
                        No hay contactos que coincidan con los filtros aplicados.
                    @else
                        Aún no se han recibido solicitudes de contacto.
                    @endif
                </p>
                @if($searchKey || ($reviewed ?? '') !== '')
                    <a href="{{ route('support.contacts') }}" class="btn btn-outline-secondary">
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
