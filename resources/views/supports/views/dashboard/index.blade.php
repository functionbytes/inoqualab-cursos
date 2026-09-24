@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Panel de soporte',
        'description' => 'Resumen general de la plataforma',
    ])
@endsection

@section('content')

    <div class="widget-content">

        {{-- Métricas --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Cursos</div>
                            <div class="dashboard-metric-value">{{ number_format($courses) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Empresas</div>
                            <div class="dashboard-metric-value">{{ number_format($enterprises) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-lines"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Blogs</div>
                            <div class="dashboard-metric-value">{{ number_format($blogs) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Clientes</div>
                            <div class="dashboard-metric-value">{{ number_format($usercustomers) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Administradores</div>
                            <div class="dashboard-metric-value">{{ number_format($useradmins) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-bag-shopping"></i>
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Pedidos</div>
                            <div class="dashboard-metric-value">{{ number_format($orders) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Solicitudes de contacto --}}
        <div class="card">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Solicitudes de contacto</h6>
                <p class="text-muted mb-0">Últimas solicitudes recibidas por soporte</p>
            </div>
            <div class="card-body p-0">
                @if($contacts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($contact->firstname.' '.$contact->lastname)), 12, '...') }}</div>
                                            <div class="text-muted small">{{ $contact->slack }}</div>
                                        </td>
                                        <td>
                                            @if($contact->reviewed == 1)
                                                <span class="badge bg-success-subtle text-success">Gestionado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                            @endif
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
                                                        <a class="dropdown-item" href="{{ route('support.contacts.edit', $contact->slack) }}">Editar</a>
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
                        <h5 class="fw-bold mb-2">No hay solicitudes</h5>
                        <p class="text-muted mb-0">Aún no se han recibido solicitudes de contacto.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/dashboard/index.css') }}">
@endpush
