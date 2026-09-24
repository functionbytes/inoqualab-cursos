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

                @if($errors->any())
                    <div class="alert alert-danger border-0 mb-3 py-2">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Destinatarios de notificaciones</h6>
                        <p class="text-muted small mb-0">Personas a las que se avisa por correo sobre los reportes programados de analytics. Cada lista es independiente: puedes dejar una vacía.</p>
                    </div>

                    {{-- Reporte enviado --}}
                    <div class="card-body">
                        <div class="repeater-head">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Reporte enviado</h6>
                                <p class="text-muted small mb-0">Reciben un aviso cuando un reporte programado se genera y se envía sin errores.</p>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm repeater-add add-email" data-target="#sent-emails-container"
                                    title="Agregar destinatario" aria-label="Agregar destinatario">
                                {!! \App\Html\IconHelper::render('plus') !!}
                            </button>
                        </div>

                        <div class="repeater-list emails-container" id="sent-emails-container" data-name="sent_emails[]">
                            @foreach($sentEmails as $i => $email)
                                <div class="email-row">
                                    <div class="repeater-row">
                                        <input type="email" class="form-control @error('sent_emails.'.$i) is-invalid @enderror"
                                               name="sent_emails[]" value="{{ $email }}" placeholder="correo@ejemplo.com" maxlength="255">
                                        <button type="button" class="btn repeater-remove remove-email" title="Quitar destinatario" aria-label="Quitar destinatario">
                                            {!! \App\Html\IconHelper::render('trash') !!}
                                        </button>
                                    </div>
                                    @error('sent_emails.'.$i)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        <p class="repeater-empty text-muted small mb-0 @if(count($sentEmails)) d-none @endif">Sin destinatarios. Usa el botón + para agregar el primero.</p>
                    </div>

                    <hr class="my-0">

                    {{-- Error en reporte --}}
                    <div class="card-body">
                        <div class="repeater-head">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Error en reporte</h6>
                                <p class="text-muted small mb-0">Reciben un aviso cuando un reporte programado falla. Conviene tener al menos uno para enterarse a tiempo.</p>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm repeater-add add-email" data-target="#failed-emails-container"
                                    title="Agregar destinatario" aria-label="Agregar destinatario">
                                {!! \App\Html\IconHelper::render('plus') !!}
                            </button>
                        </div>

                        <div class="repeater-list emails-container" id="failed-emails-container" data-name="failed_emails[]">
                            @foreach($failedEmails as $i => $email)
                                <div class="email-row">
                                    <div class="repeater-row">
                                        <input type="email" class="form-control @error('failed_emails.'.$i) is-invalid @enderror"
                                               name="failed_emails[]" value="{{ $email }}" placeholder="correo@ejemplo.com" maxlength="255">
                                        <button type="button" class="btn repeater-remove remove-email" title="Quitar destinatario" aria-label="Quitar destinatario">
                                            {!! \App\Html\IconHelper::render('trash') !!}
                                        </button>
                                    </div>
                                    @error('failed_emails.'.$i)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        <p class="repeater-empty text-muted small mb-0 @if(count($failedEmails)) d-none @endif">Sin destinatarios. Nadie se enterará si un reporte falla; usa el botón + para agregar uno.</p>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar ajustes
                        </button>
                    </div>

                </div>

            </form>

            <template id="emailRowTemplate">
                <div class="email-row">
                    <div class="repeater-row">
                        <input type="email" class="form-control" placeholder="correo@ejemplo.com" maxlength="255">
                        <button type="button" class="btn repeater-remove remove-email" title="Quitar destinatario" aria-label="Quitar destinatario">
                            {!! \App\Html\IconHelper::render('trash') !!}
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Reportes programados</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Estos avisos son aparte del reporte en sí: cada reporte programado se envía al correo que tiene configurado.</p>
                    <a href="{{ route('manager.settings.analytics.schedules.index') }}" class="btn btn-primary w-100">
                        Ver reportes programados
                    </a>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/analytics/notifications.js') }}?v={{ @filemtime(public_path('managers/js/views/settings/analytics/notifications.js')) ?: '1' }}"></script>
@endpush
