@extends('layouts.managers')

@section('content')

    @if($user)
    @else
    @endif

    <div class="row g-3">

        {{-- Columna principal: vista previa --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold">Vista previa del correo</h6>
                            <p class="text-muted">Contenido exacto enviado al destinatario</p>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="btnDesktopView" data-width="100%"
                                    title="Vista Desktop">
                                <i class="fas fa-desktop"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnMobileView" data-width="375px"
                                    title="Vista Móvil (375px)">
                                <i class="fas fa-mobile-screen"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 bg-light">
                    <div class="d-flex justify-content-center p-4" id="previewWrapper">
                        {{-- body_html es el HTML EXACTO de cualquier correo saliente (capturado
                        indiscriminadamente por LogMailSent), incluidas plantillas/campañas
                        editables por managers -- no se purifica a propósito (por diseño esta
                        vista muestra el contenido exacto enviado). Se aísla en un iframe
                        sandboxeado sin allow-scripts en vez de inyectarlo en el DOM del panel,
                        para que ningún <script> embebido pueda ejecutarse. --}}
                        <iframe id="previewContainer" sandbox="allow-same-origin"
                                style="width:100%;max-width:100%;min-height:400px;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.1);border-radius:8px;border:0;transition:max-width .3s ease;"></iframe>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Este es el contenido exacto del correo que fue enviado.
                    </p>
                </div>
            </div>
        </div>

        {{-- Columna lateral: detalles + acciones --}}
        <div class="col-12 col-lg-4">

            {{-- Detalle del correo --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom bg-warning-subtle">
                    <h6 class="mb-0 fw-bold">Detalle del correo</h6>
                    <p class="text-muted">Información del envío</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block" style="font-size:10px;letter-spacing:.5px;text-transform:uppercase;">Asunto</small>
                            <span class="fw-semibold">{{ $log->subject }}</span>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block" style="font-size:10px;letter-spacing:.5px;text-transform:uppercase;">Destinatario</small>
                            <code class="text-primary">{{ $log->recipient_email }}</code>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block" style="font-size:10px;letter-spacing:.5px;text-transform:uppercase;">Estado</small>
                            @if($log->status === 'sent')
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Enviado correctamente
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-times me-1"></i>Fallido
                                </span>
                            @endif
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block" style="font-size:10px;letter-spacing:.5px;text-transform:uppercase;">Fecha de envío</small>
                            <span>{{ $log->sent_at ? $log->sent_at->format('d/m/Y H:i:s') : $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <br>
                            <p class="text-muted">{{ $log->sent_at ? $log->sent_at->diffForHumans() : $log->created_at->diffForHumans() }}</p>
                        </div>
                        @if($log->error_message)
                            <div class="list-group-item px-3 py-2">
                                <small class="text-danger fw-semibold d-block" style="font-size:10px;letter-spacing:.5px;text-transform:uppercase;">Error</small>
                                <div class="alert alert-danger mb-0 small mt-1 py-2">{{ $log->error_message }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Acciones</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnPrint">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                    @if($user)
                        <a href="{{ route('manager.users.emails', $user->slack) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver al historial
                        </a>
                        <a href="{{ route('manager.users.edit', $user->slack) }}" class="btn btn-primary">
                            <i class="fas fa-user me-1"></i>Ver usuario
                        </a>
                    @else
                        <a href="{{ route('manager.users') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver a usuarios
                        </a>
                    @endif
                </div>
            </div>

            {{-- Datos del usuario --}}
            @if($user)
                <div class="card">
                    <div class="card-header p-3 border-bottom">
                        <h6 class="mb-0 fw-bold">Usuario</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Nombre</small>
                                <span>{{ $user->firstname }} {{ $user->lastname }}</span>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Correo</small>
                                <code class="text-primary small">{{ $user->email }}</code>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Rol</small>
                                <span class="badge bg-primary-subtle text-primary">{{ ucfirst($user->role) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

@push('css')
<style>
    @media print {
        .col-lg-4, .card-header, .card-footer { display: none !important; }
        #previewWrapper { padding: 0 !important; }
        #previewContainer { box-shadow: none !important; border-radius: 0 !important; }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    var previewFrame = document.getElementById('previewContainer');
    previewFrame.srcdoc = @json($log->body_html ?? '');
    previewFrame.addEventListener('load', function () {
        try {
            var height = previewFrame.contentDocument.documentElement.scrollHeight;
            previewFrame.style.height = Math.max(height, 400) + 'px';
        } catch (e) {
            // Si el navegador bloquea el acceso al documento, se queda con min-height.
        }
    });

    $('#btnDesktopView').on('click', function () {
        $('#previewContainer').css('max-width', '100%');
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    $('#btnMobileView').on('click', function () {
        $('#previewContainer').css('max-width', '375px');
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    $('#btnPrint').on('click', function () {
        window.print();
    });
});
</script>
@endpush

@endsection
