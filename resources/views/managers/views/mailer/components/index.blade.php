@extends('layouts.managers')

@section('title', 'Componentes de email')

@section('page_header')
    @php ob_start(); @endphp
    @can('newsletters.create')
        <a href="{{ route('mailers.components.create') }}" class="btn btn-primary btn-icon" title="Nuevo componente" aria-label="Nuevo componente">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Componentes de email',
        'description' => 'Gestiona header, footer y otros componentes reutilizables para tus plantillas de email',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

<div class="widget-content searchable-container list">

    <div id="ajax-table-root">
        @include('managers.views.mailer.components._table')
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

<div id="bulk-config" class="d-none" data-bulk-url="{{ route('mailers.components.bulk-action') }}"></div>

@include('managers.includes.bulk-toolbar-modal', [
    'bulkEntityLabel' => 'componente(s)',
    'bulkActions' => [
        ['value' => 'enable', 'label' => 'Habilitar'],
        ['value' => 'disable', 'label' => 'Deshabilitar'],
        ['value' => 'delete', 'label' => 'Eliminar'],
    ],
])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/components/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/components/index.js') }}"></script>
@endpush
