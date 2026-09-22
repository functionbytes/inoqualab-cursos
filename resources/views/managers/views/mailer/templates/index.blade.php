@extends('layouts.managers')

@section('title', 'Plantillas de email')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Plantillas de email',
        'description' => 'Gestiona plantillas de email para documentos, órdenes y notificaciones',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

                <div id="ajax-table-root">
            @include('managers.views.mailer.templates._table')
        </div>
    </div>

    <div id="bulk-config" class="d-none"
         data-bulk-url="{{ route('mailers.templates.bulk-action') }}"
         data-send-test-base-url="{{ url('settings/mailers/templates') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'plantilla(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Send Test Modal --}}
    <div class="modal fade" id="modalSendTest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="sendTestForm" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Enviar email de prueba</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0">
                            <strong>Plantilla:</strong> <span id="sendTestTemplateName"></span><br>
                            <strong>Asunto:</strong> <span id="sendTestTemplateSubject"></span>
                        </div>
                        <div class="mb-3">
                            <label for="send_test_email" class="form-label fw-semibold">Email de destino</label>
                            <input type="email" class="form-control" id="send_test_email"
                                   name="test_email" placeholder="tu@email.com" required>
                            <small class="form-text text-muted">Se enviará un email con variables de ejemplo</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-1" id="sendTestSubmitBtn">
                            Enviar ahora
                        </button>
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
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

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/templates/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/mailer/templates/index.js') }}"></script>
@endpush
