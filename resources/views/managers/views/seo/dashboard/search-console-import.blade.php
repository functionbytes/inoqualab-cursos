@extends('layouts.managers')

@section('title', 'Importar Search Console')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Importar Search Console',
        'breadcrumbs' => [
            ['label' => 'Dashboard SEO', 'url' => route('manager.seo.dashboard')],
            ['label' => 'Importar Search Console'],
        ],
    ])
@endsection

@section('content')

    <div class="row g-4 align-items-start"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Importar datos de Search Console</h6>
                    <p class="text-muted small mb-0">
                        Sube un archivo CSV exportado desde Google Search Console para vincular métricas SEO a tus páginas.
                    </p>
                </div>
                <div class="card-body">

                    <div class="mb-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 icon-box-80">
                            <i class="fab fa-google fa-3x text-primary"></i>
                        </div>
                    </div>

                    <form id="import-form" action="{{ route('manager.seo.search-console.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-0">
                            <label for="csv_file" class="form-label fw-semibold">Archivo CSV de Search Console</label>
                            <input type="file" id="csv_file" name="csv_file"
                                   class="form-control form-control-lg @error('csv_file') is-invalid @enderror"
                                   accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p class="text-muted">Máximo 5 MB. Formatos aceptados: .csv, .txt</p>
                        </div>

                    </form>
                </div>

                <div class="card-footer d-flex flex-column gap-2">
                    <button type="submit" form="import-form" class="btn btn-primary w-100 py-2">
                        Importar datos
                    </button>
                    <a href="{{ route('manager.seo.report.index') }}" class="btn btn-light border w-100 py-2">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Cómo exportar desde Search Console</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center icon-box-32">1</span>
                        </div>
                        <div>
                            <strong class="d-block">Accede a Google Search Console</strong>
                            <p class="text-muted mb-0">Ve a tu propiedad en Search Console.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center icon-box-32">2</span>
                        </div>
                        <div>
                            <strong class="d-block">Resultados de búsqueda → Páginas</strong>
                            <p class="text-muted mb-0">Abre el informe de Resultados de búsqueda y selecciona la pestaña <strong>Páginas</strong>.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center icon-box-32">3</span>
                        </div>
                        <div>
                            <strong class="d-block">Exportar → Descargar CSV</strong>
                            <p class="text-muted mb-0">Haz clic en el botón Exportar y elige la opción CSV.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="me-3">
                            <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center icon-box-32">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <div>
                            <strong class="d-block">Sube el archivo aquí</strong>
                            <p class="text-muted mb-0">Los datos se vincularán a las páginas por su URL canónica.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Formato esperado del CSV</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach([
                            ['Page', 'URL de la página', 'https://ejemplo.com/pagina', 'URL canónica completa', true],
                            ['Clicks', 'Clics totales', '120', 'Número entero', true],
                            ['Impressions', 'Impresiones', '3400', 'Número entero', true],
                            ['CTR', 'Tasa de clics', '3.5%', 'Porcentaje', false],
                            ['Position', 'Posición media', '8.2', 'Número decimal', false],
                        ] as $col)
                            <div class="list-group-item bg-white rounded mb-2 border px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                        <h6 class="mb-0">{{ $col[0] }}</h6>
                                        <p class="text-muted mb-0">{{ $col[1] }}</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 text-nowrap">
                                        <code>{{ $col[2] }}</code>
                                        <small class="text-muted d-none d-md-inline">{{ $col[3] }}</small>
                                        <span class="badge {{ $col[4] ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">{{ $col[4] ? 'requerido' : 'opcional' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Se aceptan también exportaciones en español (Página, Clics, Impresiones, Posición).
                    </small>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/dashboard/search-console-import.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
@endpush
