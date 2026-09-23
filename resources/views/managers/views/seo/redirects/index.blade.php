@extends('layouts.managers')

@section('title', 'Redirects SEO')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modalRedirect"
                                title="Nuevo redirect" aria-label="Nuevo redirect">{!! \App\Html\IconHelper::render('plus') !!}</button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Redirects SEO',
        'description' => 'Gestiona las redirecciones HTTP del sitio',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list">

                <div id="ajax-table-root">
            @include('managers.views.seo.redirects._table')
        </div>

    </div>

    {{-- Modal crear/editar redirect --}}
    <div class="modal fade" id="modalRedirect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRedirectTitle">Nuevo redirect</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formRedirect"
                          data-store-url="{{ route('manager.seo.redirects.store') }}"
                          data-update-url-template="{{ route('manager.seo.redirects.update', ':id') }}">
                        <input type="hidden" id="redirect-id" name="redirect_id" value="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de origen</label>
                            <input type="text" class="form-control" id="source_path" name="source_path"
                                   placeholder="/ruta-antigua">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de destino</label>
                            <input type="text" class="form-control" id="target_path" name="target_path"
                                   placeholder="/nueva-ruta o https://ejemplo.com/pagina">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Codigo de estado</label>
                            <select class="form-select" id="status_code" name="status_code">
                                <option value="301">301 — Permanente</option>
                                <option value="302">302 — Temporal</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_regex" name="is_regex">
                                    <label class="form-check-label" for="is_regex">Es expresion regular</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_wildcard" name="is_wildcard">
                                    <label class="form-check-label" for="is_wildcard">Usa wildcard (*)</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Nota interna</label>
                            <textarea class="form-control" id="redirect-note" name="note" rows="2"
                                      placeholder="Motivo o contexto del redirect (opcional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-redirect">
                        Guardar redirect
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulk-config" class="d-none" data-bulk-destroy-url="{{ route('manager.seo.redirects.bulk-destroy') }}"></div>

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <button type="button" class="btn btn-primary shadow-lg px-4"
                data-bs-toggle="modal" data-bs-target="#bulk-modal">
            <span data-bulk-count>0</span> seleccionado(s) &mdash; Aplicar accion
        </button>
    </div>

    {{-- Bulk modal --}}
    <div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accion masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Se aplicara la accion sobre
                        <strong><span data-bulk-count>0</span> redirect(s)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar accion...</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="bulk-apply-btn" type="button" class="btn btn-primary w-100 mb-2">Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/redirects/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/redirects/index.js') }}"></script>
@endpush

