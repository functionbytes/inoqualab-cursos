@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('distributor.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary btn-icon" title="Nuevo usuario" aria-label="Nuevo usuario">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('distributors.includes.card', [
        'title' => 'Usuarios '.$enterprise->title,
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($available ?? '') !== '' && ($available ?? null) !== null) {
                        $filterChips[] = [
                            'label' => 'Estado: '.($available == '1' ? 'Activo' : 'Inactivo'),
                            'clear_url' => url()->current().'?'.http_build_query(request()->except('available')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('distributor.enterprises.users', $enterprise->slack) }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Estado</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_available" value="" {{ ($available ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_available" value="1" {{ ($available ?? '') === '1' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Activo</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Inactivo</span>
                            </label>
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por nombre, correo o identificacion...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Identificacion</th>
                            <th>Usuario</th>
                            <th>Correo electronico</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($users as $user)
                            <tr>
                                <td>{{ ucfirst($user->identification) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($user->firstname.' '.$user->lastname)), 12, '...') }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $user->available == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $user->available == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if ($user->role == 'customer')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('distributor.enterprises.users.results', $user->slack) }}">Resultados</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('distributor.enterprises.users.certificates', $user->slack) }}">Certificados</a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="{{ route('distributor.enterprises.users.courses', $user->slack) }}">Cursos</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('distributor.enterprises.users.view', $user->slack) }}">Visualizar</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('distributor.enterprises.users.edit', $user->slack) }}">Editar</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $users,
                'itemLabel' => 'usuarios',
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/users/users/index.js') }}"></script>
@endpush


