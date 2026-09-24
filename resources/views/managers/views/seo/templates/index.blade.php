@extends('layouts.managers')

@section('title', 'Plantillas SEO')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.seo.templates.create') }}" class="btn btn-primary btn-icon" title="Nueva plantilla" aria-label="Nueva plantilla">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Plantillas SEO',
        'description' => 'Patrones reutilizables para títulos y descripciones meta',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.seo.templates._table')
        </div>
    </div>

    {{-- Apply to metas modal --}}
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar plantilla a metas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="apply-modal-body">
                    <div class="py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Calculando registros afectados...</p>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-apply-btn" disabled>Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulk-config" class="d-none" data-bulk-url="{{ route('manager.seo.templates.bulk-action') }}"></div>

    {{-- Activar/Desactivar se quitaron del menú de acciones masivas: el
         estado ya se controla por fila con el switch de la columna
         "Estado" en _table.blade.php -- tener las dos formas era
         redundante. --}}
    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'plantilla(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/templates/index.js') }}"></script>
@endpush

