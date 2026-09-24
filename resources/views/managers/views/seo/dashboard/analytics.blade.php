@extends('layouts.managers')

@section('title', 'Analytics SEO')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('manager.seo.search-console.import') }}" class="btn btn-primary btn-icon" title="Importar datos" aria-label="Importar datos">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Analytics SEO — Google Search Console',
        'description' => 'Visualiza clics, impresiones y posiciones de tus páginas en Google',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error">

        <div class="card mb-3">
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total clics</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($gscStats->total_clicks ?? 0) }}</h4>
                                <p class="text-muted">Clics registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total impresiones</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($gscStats->total_impressions ?? 0) }}</h4>
                                <p class="text-muted">Impresiones registradas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Posición promedio</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($gscStats->avg_position ?? 0, 1) }}</h4>
                                <p class="text-muted">Posición #1 = mejor</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Páginas con datos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($gscStats->pages_with_data ?? 0) }}</h4>
                                <p class="text-muted">Páginas en GSC</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($lastUpdated)
                <div class="card-body py-2">
                    <div class="alert alert-info border-0 py-2 mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <small>Datos actualizados: {{ \Carbon\Carbon::parse($lastUpdated)->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            @endif
        </div>

        <div id="ajax-table-root">
            @include('managers.views.seo.dashboard._analytics', ['pages' => $pages])
        </div>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/dashboard/analytics.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/dashboard/analytics.js') }}"></script>
@endpush
