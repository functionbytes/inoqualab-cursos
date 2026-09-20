@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Usuarios</h5>
                        <p class="mb-0 text-muted">Usuarios de {{ Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...') }}</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('support.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary">
                            Nuevo usuario
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Usuarios</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Con certificados</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['with_certificates']) }}</h4>
                                <span class="text-muted">Ya certificados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Con inscripciones</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['with_inscriptions']) }}</h4>
                                <span class="text-muted">Con cursos matriculados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('support.enterprises.users', $enterprise->slack) }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre, correo o identificacion..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Identificacion</th>
                                    <th>Usuario</th>
                                    <th>Correo electronico</th>
                                    <th class="text-center">Actualizacion</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ ucfirst($user->identification) }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($user->firstname.' '.$user->lastname)), 12, '...') }}</div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($user->updated_at)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if(count($user->certificates) > 0)
                                                        <li>
                                                            <a class="dropdown-item {{ $user->role == 'customer' ? '' : 'd-none' }}"
                                                               href="{{ route('support.enterprises.users.results', $user->slack) }}">
                                                                Resultados
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item {{ $user->role == 'customer' ? '' : 'd-none' }}"
                                                               href="{{ route('support.enterprises.users.certificates', $user->slack) }}">
                                                                Certificados
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if(count($user->inscriptions) > 0)
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="{{ route('support.enterprises.users.courses', $user->slack) }}">
                                                                Cursos
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('support.enterprises.users.view', $user->slack) }}">
                                                            Visualizar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('support.enterprises.users.edit', $user->slack) }}">
                                                            Editar
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
                        <i class="fas fa-users fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey ?? '')
                                No se encontraron resultados
                            @else
                                No hay usuarios
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey ?? '')
                                No hay usuarios que coincidan con la busqueda.
                            @else
                                Crea el primer usuario de esta empresa.
                            @endif
                        </p>
                        @if($searchKey ?? '')
                            <a href="{{ route('support.enterprises.users', $enterprise->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('support.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary">
                                Nuevo usuario
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
                    </span>
                    {{ $users->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
