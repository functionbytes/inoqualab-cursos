@extends('layouts.managers')

@section('title', 'Reportes programados')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.settings.analytics.schedules.create') }}" class="btn btn-primary btn-icon" title="Nuevo reporte" aria-label="Nuevo reporte">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Reportes programados',
        'description' => 'Envios automaticos de reportes de analytics por email',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="schedulesPage" class="widget-content searchable-container list"
         data-bulk-action-url="{{ route('manager.settings.analytics.schedules.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.analytics.schedules._table')
        </div>

    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'reporte(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Delete modal --}}
    <div id="delete-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar eliminacion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="display-4 text-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="fw-bold mb-2" id="delete-modal-title">¿Estas seguro?</h5>
                        <p class="text-muted">Esta accion no se puede deshacer.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Confirmar eliminacion</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/analytics/schedules/index.js') }}"></script>
@endpush
