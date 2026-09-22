@extends('layouts.managers')

@section('title', 'Logs de endpoint: ' . $endpoint->name)

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Logs: ' . $endpoint->name,
        'breadcrumbs' => [
            ['label' => 'Endpoints', 'url' => route('mailers.endpoints.index')],
            ['label' => $endpoint->name],
        ],
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Endpoint info --}}
<div class="card card-body mb-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <h6 class="text-muted mb-1">Endpoint</h6>
            <h5 class="mb-0 fw-bold">{{ $endpoint->name }}</h5>
            <p class="text-muted">{{ $endpoint->slug }}</p>
        </div>
        <div class="col-md-4">
            <h6 class="text-muted mb-1">Clasificación</h6>
            <span class="badge bg-light text-primary rounded-pill py-1 px-2 me-1">{{ $endpoint->source }}</span>
            <span class="badge bg-light text-dark rounded-pill py-1 px-2">{{ $endpoint->type }}</span>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('mailers.endpoints.edit', $endpoint) }}" class="btn btn-sm btn-outline-primary me-1">
                Editar
            </a>
            <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-sm btn-light">
                Atrás
            </a>
        </div>
    </div>
</div>

<div class="card mb-3">
    {{-- Stats --}}
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-2">Total logs</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                        <p class="text-muted">Registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Exitosos</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['success'] }}</h4>
                        <p class="text-muted">Enviados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Fallidos</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['failed'] }}</h4>
                        <p class="text-muted">Con errores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title text-info mb-2">Tasa de éxito</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['success_rate'] }}%</h4>
                        <p class="text-muted">Rendimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ajax-table-root">
    @include('managers.views.mailer.endpoints._logs', [
        'endpoint' => $endpoint,
        'logs' => $logs,
        'searchEmail' => $searchEmail ?? '',
        'filterStatus' => $filterStatus ?? '',
        'period' => $period ?? '',
    ])
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/logs.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/logs.js') }}"></script>
@endpush
