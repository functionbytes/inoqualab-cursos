@extends('layouts.managers')

@section('title', 'Variables de email')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Variables de email',
        'description' => 'Gestiona variables dinámicas que se sustituyen automáticamente en plantillas y componentes',
    ])
@endsection

@section('content')

<div class="widget-content searchable-container list">

        <div id="ajax-table-root">
        @include('managers.views.mailer.variables._table')
    </div>

</div>

{{-- Delete modal --}}
<div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4 position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <div class="mb-3 mt-2">
                    <i class="fas fa-triangle-exclamation text-warning fs-icon-lg"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Estás seguro de eliminar esto?</h5>
                <p class="text-muted mb-4">Esta acción no se puede deshacer. Todos los datos relacionados pueden eliminarse.</p>
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary w-100 mb-2">Confirmar eliminación</button>
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="bulk-config" class="d-none" data-bulk-url="{{ route('mailers.variables.bulk-action') }}"></div>

@include('managers.includes.bulk-toolbar-modal', [
    'bulkEntityLabel' => 'variable(s)',
    'bulkActions' => [
        ['value' => 'enable', 'label' => 'Habilitar'],
        ['value' => 'disable', 'label' => 'Deshabilitar'],
        ['value' => 'delete', 'label' => 'Eliminar'],
    ],
])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/variables/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/variables/index.js') }}"></script>
@endpush
