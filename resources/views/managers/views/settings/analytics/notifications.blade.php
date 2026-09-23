@extends('layouts.managers')

@section('title', 'Notificaciones de analytics')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Notificaciones de analytics'])
@endsection

@section('content')


    <div id="analyticsNotificationsPage" class="row g-4 align-items-start" data-flash-success="{{ session('success') }}">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form action="{{ route('manager.settings.analytics.notifications.update') }}" method="POST">
                @csrf

                @if(session('success'))
                    <div class="alert alert-success border-0 mb-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0 mb-3 py-2">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">

                    {{-- Reporte enviado --}}
                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte enviado</h6>
                        <p class="text-muted small mb-0">
                            Destinatarios que recibirán el correo cuando un reporte programado se genere sin errores.
                            <span class="ms-1">
                                @if(count($sentEmails) > 0)
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-light text-dark">Sin destinatarios</span>
                                @endif
                            </span>
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="emails-container" id="sent-emails-container" data-name="sent_emails[]">
                            @forelse($sentEmails as $i => $email)
                                <div class="mb-2 email-row">
                                    <div class="input-group">
                                        <input type="email"
                                               class="form-control @error('sent_emails.'.$i) is-invalid @enderror"
                                               name="sent_emails[]"
                                               value="{{ $email }}"
                                               placeholder="correo@ejemplo.com">
                                        <button type="button" class="btn btn-info remove-email">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @if($loop->last)
                                            <button type="button" class="btn btn-outline-secondary add-email">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @error('sent_emails.'.$i)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @empty
                                <div class="mb-2 email-row">
                                    <div class="input-group">
                                        <input type="email" class="form-control" name="sent_emails[]"
                                               placeholder="correo@ejemplo.com">
                                        <button type="button" class="btn btn-info remove-email">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary add-email">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="alert alert-info border-0 mb-0 py-2 small mt-3">
                            <i class="fas fa-circle-info me-1"></i>
                            Incluye el reporte adjunto en el formato configurado (PDF, Excel o CSV).
                        </div>
                    </div>

                    <hr class="my-0">

                    {{-- Error en reporte --}}
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Error en reporte</h6>
                        <p class="text-muted mb-3">
                            Destinatarios que recibirán el correo cuando un reporte falle. Se incluye el mensaje de error.
                            <span class="ms-1">
                                @if(count($failedEmails) > 0)
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-light text-dark">Sin destinatarios</span>
                                @endif
                            </span>
                        </p>

                        <div class="emails-container" id="failed-emails-container" data-name="failed_emails[]">
                            @forelse($failedEmails as $i => $email)
                                <div class="mb-2 email-row">
                                    <div class="input-group">
                                        <input type="email"
                                               class="form-control @error('failed_emails.'.$i) is-invalid @enderror"
                                               name="failed_emails[]"
                                               value="{{ $email }}"
                                               placeholder="correo@ejemplo.com">
                                        <button type="button" class="btn btn-info remove-email">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @if($loop->last)
                                            <button type="button" class="btn btn-outline-secondary add-email">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @error('failed_emails.'.$i)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @empty
                                <div class="mb-2 email-row">
                                    <div class="input-group">
                                        <input type="email" class="form-control" name="failed_emails[]"
                                               placeholder="correo@ejemplo.com">
                                        <button type="button" class="btn btn-info remove-email">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary add-email">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="alert alert-warning border-0 mb-0 py-2 small mt-3">
                            <i class="fas fa-triangle-exclamation me-1"></i>
                            Se recomienda añadir al menos un destinatario para detectar fallos en reportes programados.
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar ajustes
                        </button>
                    </div>

                </div>

            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre las notificaciones</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold mb-1">Reporte enviado</h6>
                    <p class="text-muted mb-3">Se envía cuando un reporte programado se genera y envía correctamente. Incluye el archivo adjunto en el formato configurado (PDF, Excel o CSV).</p>

                    <hr class="my-3">

                    <h6 class="fw-semibold mb-1">Error en reporte</h6>
                    <p class="text-muted mb-0">Se envía cuando ocurre un error durante la generación del reporte. El correo incluye el mensaje de error para facilitar el diagnóstico.</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Reportes programados</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Configura o revisa los reportes que se envían automáticamente.</p>
                    <a href="{{ route('manager.settings.analytics.schedules.index') }}" class="btn btn-outline-primary w-100">
                        Ver reportes
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Consejos de uso</h6>
                </div>
                <div class="card-body">
                    <ul class="text-muted mb-0 small">
                        <li class="mb-2">Añade siempre un destinatario para <strong>error en reporte</strong> para detectar problemas a tiempo.</li>
                        <li class="mb-2">Puedes añadir múltiples destinatarios en cada tipo de notificación.</li>
                        <li class="mb-2">Los correos se envían desde la dirección configurada en los ajustes de correo del sistema.</li>
                        <li class="mb-0">La notificación se activa automáticamente al añadir un destinatario.</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/analytics/notifications.js') }}"></script>
@endpush
