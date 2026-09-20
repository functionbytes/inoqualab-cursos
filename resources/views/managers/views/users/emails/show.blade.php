@extends('layouts.managers')

@section('content')

    @if($user)
    @else
    @endif

    <div class="row g-3" id="users-emails-show"
         data-config='@json(["bodyHtml" => $log->body_html ?? ""])'>

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
                                class="email-preview-frame"></iframe>
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
                            <small class="text-muted fw-semibold d-block email-detail-label">Asunto</small>
                            <span class="fw-semibold">{{ $log->subject }}</span>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block email-detail-label">Destinatario</small>
                            <code class="text-primary">{{ $log->recipient_email }}</code>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted fw-semibold d-block email-detail-label">Estado</small>
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
                            <small class="text-muted fw-semibold d-block email-detail-label">Fecha de envío</small>
                            <span>{{ $log->sent_at ? $log->sent_at->format('d/m/Y H:i:s') : $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <br>
                            <p class="text-muted">{{ $log->sent_at ? $log->sent_at->diffForHumans() : $log->created_at->diffForHumans() }}</p>
                        </div>
                        @if($log->error_message)
                            <div class="list-group-item px-3 py-2">
                                <small class="text-danger fw-semibold d-block email-detail-label">Error</small>
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
                        Imprimir
                    </button>
                    @if($user)
                        <a href="{{ route('manager.users.emails', $user->slack) }}" class="btn btn-secondary">
                            Volver al historial
                        </a>
                        <a href="{{ route('manager.users.edit', $user->slack) }}" class="btn btn-primary">
                            Ver usuario
                        </a>
                    @else
                        <a href="{{ route('manager.users') }}" class="btn btn-secondary">
                            Volver a usuarios
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
<link rel="stylesheet" href="{{ asset('managers/css/views/users/emails/show.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/users/emails/show.js') }}"></script>
@endpush

@endsection
