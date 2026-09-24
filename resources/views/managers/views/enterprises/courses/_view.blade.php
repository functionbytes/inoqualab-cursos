{{--
    Partial AJAX: filtros + tabla + paginacion de estudiantes inscritos en un
    curso de empresa.

    Se incluye normalmente desde view.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($culminate ?? '') !== '') {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($culminate == '1' ? 'Culminado' : 'Pendiente'),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('culminate')),
                ];
            }
        @endphp
        <form method="GET" action="{{ Request::url() }}" id="searchForm">

            <input type="hidden" name="culminate" id="filterCulminate" value="{{ $culminate ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminate" value="" {{ ($culminate ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminate" value="1" {{ ($culminate ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Culminado</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminate" value="0" {{ ($culminate ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Pendiente</span>
                    </label>
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por nombre, correo o identificación...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Identificación</th>
                            <th>Cliente</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Año</th>
                            <th class="text-center">Porcentaje</th>
                            <th>Fecha inicio</th>
                            <th>Fecha final</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ ucfirst($user->identification) }}</td>
                                <td class="fw-semibold">
                                    {{ Str::words(Str::title(Str::lower($user->firstname . ' ' . $user->lastname)), 12, '...') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $user->culminated == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $user->culminated == 1 ? 'Culminado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ date('Y', strtotime($user->enroll_start)) }}</td>
                                <td class="text-center">{{ round($user->percent, 2) }}%</td>
                                <td>{{ date('Y-m-d', strtotime($user->enroll_start)) }}</td>
                                <td>
                                    {{ $user->enroll_culminated === null ? 'Pendiente' : date('Y-m-d', strtotime($user->enroll_culminated)) }}
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($user->enroll_culminated !== null)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.certificate.user', $user->inscription_slack) }}">
                                                        Certificado
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="{{ route('manager.enterprises.courses.progress', $user->inscription_slack) }}">
                                                    Reporte
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('manager.enterprises.postpone.courses', $user->order_slack) }}">
                                                    Ampliar
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') !== '' || ($culminate ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay usuarios inscritos
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') !== '' || ($culminate ?? '') !== '')
                        Ningún usuario coincide con los filtros aplicados.
                    @else
                        Los usuarios inscritos en este curso aparecerán aquí.
                    @endif
                </p>
                @if(($searchKey ?? '') !== '' || ($culminate ?? '') !== '')
                    <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $users,
        'itemLabel' => 'usuarios',
    ])

</div>
