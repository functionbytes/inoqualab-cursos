@extends('layouts.managers')

@section('content')

    <div class="widget-content dashboard-content">

        {{-- Banner de bienvenida --}}
        <div class="dashboard-welcome-banner mb-4">
            <div class="dashboard-welcome-content">
                <div class="dashboard-welcome-eyebrow">{{ $enterprises->title }}</div>
                <h1 class="dashboard-welcome-title">Bienvenido de nuevo a tu tablero</h1>
                <p class="dashboard-welcome-text">Aquí encontrarás un resumen de tus usuarios, cursos y certificaciones. Gestiona todo desde un solo lugar y mantente al día con tus metas de aprendizaje.</p>
            </div>
            <div class="dashboard-welcome-glow dashboard-welcome-glow-1"></div>
            <div class="dashboard-welcome-glow dashboard-welcome-glow-2"></div>
        </div>

        {{-- Usuarios recientes --}}
        <div class="card">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Usuarios recientes</h6>
                <p class="text-muted mb-0">Últimos usuarios registrados en tu empresa</p>
            </div>
            <div class="card-body p-0">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Identificación</th>
                                    <th>Cliente</th>
                                    <th>Correo electrónico</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="text-muted">{{ $user->identification }}</td>
                                        <td class="fw-semibold">{{ Str::words(Str::upper(Str::lower($user->firstname.' '.$user->lastname)), 12, '...') }}</td>
                                        <td class="text-muted">{{ $user->email }}</td>
                                        <td class="text-muted">{{ date('Y-m-d', strtotime($user->updated_at)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-users', 48) !!}</div>
                        <h5 class="fw-bold mb-2">No hay usuarios registrados</h5>
                        <p class="text-muted mb-0">Aún no tienes usuarios registrados en tu empresa.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('enterprises/css/views/dashboard/index.css') }}">
@endpush
