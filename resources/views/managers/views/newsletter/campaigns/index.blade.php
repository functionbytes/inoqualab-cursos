@extends('layouts.managers')

@section('title', 'Campañas de newsletter')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('manager.newsletter.index') }}">
                Suscriptores
            </a>
            <a class="dropdown-item" href="{{ route('manager.newsletter.lists.index') }}">
                Listas
            </a>
            <a class="dropdown-item" href="{{ route('manager.newsletter.remarketing') }}">
                Remarketing
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.create') }}">
                + Nueva campaña
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Campañas de newsletter',
        'description' => 'Envía newsletters a tus suscriptores activos',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="campaigns-page"
         data-bulk-url="{{ route('manager.newsletter.campaigns.bulk-action') }}"
         data-has-sending="{{ $hasSending ? 'true' : 'false' }}">

                <div id="ajax-table-root">
            @include('managers.views.newsletter.campaigns._table')
        </div>
    </div>

{{-- Modal: filtros avanzados --}}


{{-- Modal confirmación de envío --}}
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">¿Enviar la campaña <strong id="sendCampaignName"></strong>?</p>
                <p class="text-muted small mb-0">Esta acción no se puede deshacer. Se enviará a todos los suscriptores activos.</p>
            </div>
            <div class="modal-footer d-block">
                <button type="button" class="btn btn-danger w-100 mb-2" id="btnConfirmSend">
                    Sí, enviar ahora
                </button>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@include('managers.includes.bulk-toolbar-modal', [
    'bulkEntityLabel' => 'campaña(s)',
    'bulkActions' => [
        ['value' => 'delete', 'label' => 'Eliminar'],
    ],
])

@include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/campaigns/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/campaigns/index.js') }}"></script>
@endpush
