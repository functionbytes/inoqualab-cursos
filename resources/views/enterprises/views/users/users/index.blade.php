@extends('layouts.managers')

@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Usuarios'])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('enterprise.users') }}" id="searchForm">
                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por nombre, correo o identificacion...',
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
                            <th>Cliente</th>
                            <th>Correo electronico</th>
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
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('enterprise.users.results', $user->slack) }}">Resultados</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('enterprise.users.certificates', $user->slack) }}">Certificados</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('enterprise.users.edit', $user->slack) }}">Gestionar</a>
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
    <script src="{{ asset('enterprises/js/views/users/users/index.js') }}"></script>
@endpush
