@extends('layouts.managers')

@section('title', 'Sitemap XML')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="row g-4 align-items-start">

            {{-- Columna izquierda --}}
            <div class="col-lg-8">

                {{-- Cards de estado --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Estado del sitemap</h6>
                        <p class="text-muted mb-3">Información actual sobre los sitemaps disponibles y su caché.</p>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Total sitemaps</h6>
                                        <h4 class="mb-1 fw-bold">{{ count($sitemaps) }}</h4>
                                        <p class="text-muted">Tipos disponibles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Sitemaps en caché</h6>
                                        <h4 class="mb-1 fw-bold">
                                            {{ collect($sitemaps)->where('has_cache', true)->count() }}
                                            <small class="fs-6 text-muted fw-normal">/ {{ count($sitemaps) }}</small>
                                        </h4>
                                        <p class="text-muted">Con caché activo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de sitemaps --}}
                <div class="card">
                    <div class="card-body border-bottom pb-3">
                        <h6 class="fw-bold text-dark mb-1">Sitemaps disponibles</h6>
                        <p class="text-muted mb-0">URLs de cada tipo de sitemap generado por el sistema.</p>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>URL</th>
                                        <th class="text-center">Estado caché</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sitemaps as $sitemap)
                                        <tr>
                                            <td class="fw-semibold">{{ $sitemap['name'] }}</td>
                                            <td>
                                                <code class="small text-break">{{ $sitemap['url'] }}</code>
                                            </td>
                                            <td class="text-center">
                                                @if($sitemap['has_cache'])
                                                    <span class="badge bg-success">En caché</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Sin caché</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ $sitemap['url'] }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="fas fa-sitemap fs-2 d-block mb-2 opacity-50"></i>
                                                No hay sitemaps disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Columna derecha --}}
            <div class="col-lg-4">

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">Forzar regeneración</h6>
                        <p class="text-muted mb-3">Regenera todos los sitemaps con el contenido actual del sitio.</p>
                        <button type="button" id="btn-generate" class="btn btn-primary w-100"
                                data-generate-url="{{ route('manager.seo.sitemap.generate') }}">
                            Forzar regeneración
                        </button>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">Limpiar caché</h6>
                        <p class="text-muted mb-3">Limpia la caché del sitemap. Se volverá a generar automáticamente al siguiente acceso.</p>
                        <button type="button" id="btn-clear-cache" class="btn btn-outline-secondary w-100"
                                data-clear-cache-url="{{ route('manager.seo.sitemap.clear-cache') }}">
                            Limpiar caché
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/seo/sitemap/index.js') }}"></script>
@endpush
