@extends('layouts.managers')

@section('title', 'Alertas SEO')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-outline-secondary" id="acknowledge-all-btn">
                                Marcar todas como revisadas
                            </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Alertas SEO',
        'description' => 'Revisión de problemas detectados en el sitio',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.seo.alerts._table')
        </div>
    </div>

    

    {{-- Confirm acknowledge all --}}
    <div class="modal fade" id="acknowledgeAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Marcar todas como revisadas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning mb-3">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <p class="mb-0">Se marcarán <strong>{{ $stats['unacknowledged'] }} alerta(s)</strong> como revisadas. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-acknowledge-all">Confirmar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulk-config" class="d-none"
         data-bulk-url="{{ route('manager.seo.alerts.bulk-action') }}"
         data-acknowledge-all-url="{{ route('manager.seo.alerts.acknowledge-all') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'alerta(s)',
        'bulkActions' => [
            ['value' => 'acknowledge', 'label' => 'Marcar como revisadas'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/alerts/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/alerts/index.js') }}"></script>
@endpush
