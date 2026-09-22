@extends('layouts.managers')

@section('title', 'Email endpoints')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Endpoints configurados',
        'description' => 'Gestiona los endpoints para enviar correos desde aplicaciones externas',
    ])
@endsection

@section('content')

<div class="widget-content searchable-container list">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

        <div id="ajax-table-root">
        @include('managers.views.mailer.endpoints._table')
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

<div id="bulk-config" class="d-none" data-bulk-url="{{ route('mailers.endpoints.bulk-action') }}"></div>

@include('managers.includes.bulk-toolbar-modal', [
    'bulkEntityLabel' => 'endpoint(s)',
    'bulkActions' => [
        ['value' => 'activate', 'label' => 'Activar'],
        ['value' => 'deactivate', 'label' => 'Desactivar'],
        ['value' => 'delete', 'label' => 'Eliminar'],
    ],
])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/index.js') }}"></script>
@endpush
