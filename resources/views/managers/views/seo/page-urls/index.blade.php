@extends('layouts.managers')

@section('title', 'URLs del sitio')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/page-urls/index.css') }}">
@endpush

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'URLs del sitio',
        'description' => 'Páginas del sitio con su estado de configuración SEO',
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error"
         data-bulk-generate-url="{{ route('manager.seo.orphans.bulk-generate') }}">

                <div id="ajax-table-root">
            @include('managers.views.seo.page-urls._table')
        </div>

    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'página(s) sin SEO',
        'bulkActions' => [
            ['value' => 'generate_seo', 'label' => 'Generar SEO'],
        ],
    ])

    {{-- Modal crear redirect --}}
    <div class="modal fade" id="modalRedirect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear redirect</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Origen: <code id="redirect-source-display" class="text-primary"></code>
                    </p>
                    <form id="formRedirect"
                          action="{{ route('manager.seo.redirects.store') }}">
                        <input type="hidden" id="redirect-source" name="source_path">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de destino</label>
                            <input type="text" class="form-control" id="redirect-target" name="target_path"
                                   placeholder="/nueva-ruta o https://ejemplo.com/pagina">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Código de redirección</label>
                            <select class="form-select" id="redirect-code" name="status_code">
                                <option value="301">301 — Permanente</option>
                                <option value="302">302 — Temporal</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-block">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-redirect">
                        Guardar redirect
                    </button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/page-urls/index.js') }}"></script>
@endpush
