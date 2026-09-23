@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Smtp'])
@endsection

@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
        <form id="formEmails" data-update-url="{{ route('manager.settings.emails.update') }}">
            @csrf

            <div class="card">

                {{-- Estado del servicio --}}
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Estado del servicio</h6>
                    <p class="text-muted small mb-0">Habilita o deshabilita el envío de correos (SMTP) en el sitio.</p>
                </div>

                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="mail_status" id="mail_status"
                            @if(setting('mail_status') !== 'false') checked @endif>
                        <label class="form-check-label fw-semibold" for="mail_status">Habilitar envío de correos (SMTP)</label>
                    </div>
                    <small class="text-muted d-block">Si se deshabilita, los correos no se envían realmente: quedan registrados en el log.</small>
                </div>

                <div id="smtpFields" class="{{ setting('mail_status') !== 'false' ? '' : 'd-none' }}">

                <hr class="my-0">

                {{-- SMTP / ENVÍO DE CORREOS --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Configuración SMTP (envío de correos)</h6>
                    <p class="text-muted mb-3">Credenciales del servidor de correo saliente. Compatible con Mailjet, Mailgun, SendGrid, Gmail u otro servidor SMTP.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="mail_host" class="form-label fw-semibold">Host SMTP</label>
                            <input type="text" class="form-control" id="mail_host" name="mail_host"
                                value="{{ setting('mail_host') }}" placeholder="in-v3.mailjet.com">
                        </div>
                        <div class="col-md-3">
                            <label for="mail_port" class="form-label fw-semibold">Puerto</label>
                            <input type="number" class="form-control" id="mail_port" name="mail_port"
                                value="{{ setting('mail_port') ?: 587 }}" placeholder="587">
                        </div>
                        <div class="col-md-3">
                            <label for="mail_encryption" class="form-label fw-semibold">Cifrado</label>
                            <select class="form-select select2" id="mail_encryption" name="mail_encryption">
                                <option value="tls"      {{ setting('mail_encryption') == 'tls'      ? 'selected' : '' }}>TLS (recomendado)</option>
                                <option value="ssl"      {{ setting('mail_encryption') == 'ssl'      ? 'selected' : '' }}>SSL</option>
                                <option value="starttls" {{ setting('mail_encryption') == 'starttls' ? 'selected' : '' }}>STARTTLS</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_username" class="form-label fw-semibold">Usuario / API Key</label>
                            <input type="text" class="form-control" id="mail_username" name="mail_username"
                                value="{{ setting('mail_username') }}" placeholder="API Key de Mailjet">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_password" class="form-label fw-semibold">Contraseña / Secret Key</label>
                            <input type="password" class="form-control" id="mail_password" name="mail_password"
                                placeholder="Dejar vacío para no cambiar">
                            <small class="text-muted d-block mt-1">Dejar en blanco si no deseas cambiar la contraseña actual.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_from_address" class="form-label fw-semibold">Correo remitente (From)</label>
                            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                                value="{{ setting('mail_from_address') }}" placeholder="no-reply@tudominio.com">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_from_name" class="form-label fw-semibold">Nombre remitente</label>
                            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                                value="{{ setting('mail_from_name') }}" placeholder="Nombre de tu plataforma">
                        </div>
                    </div>
                </div>

                </div>{{-- /#smtpFields --}}

                <hr class="my-0">

                {{-- IMAP / RECEPCIÓN DE CORREOS --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Configuración IMAP (recepción de correos)</h6>
                    <p class="text-muted mb-3">Conexión al buzón de entrada. Usado para procesar correos entrantes y crear órdenes/matrículas automáticamente.</p>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="imap_status" id="imap_status"
                            @if(setting('imap_status') == 'true') checked @endif>
                        <label class="form-check-label fw-semibold" for="imap_status">IMAP activo</label>
                    </div>
                    <small class="text-muted d-block">Activar el procesamiento de correos entrantes para crear órdenes y matrículas.</small>
                </div>

                <div id="imapFields" class="{{ setting('imap_status') == 'true' ? '' : 'd-none' }}">

                <hr class="my-0">

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="imap_host" class="form-label fw-semibold">Host IMAP</label>
                            <input type="text" class="form-control" id="imap_host" name="imap_host"
                                value="{{ setting('imap_host') }}" placeholder="imap.gmail.com">
                        </div>
                        <div class="col-md-3">
                            <label for="imap_port" class="form-label fw-semibold">Puerto</label>
                            <input type="text" class="form-control" id="imap_port" name="imap_port"
                                value="{{ setting('imap_port') }}" placeholder="993">
                        </div>
                        <div class="col-md-3">
                            <label for="imap_encryption" class="form-label fw-semibold">Cifrado</label>
                            <input type="text" class="form-control" id="imap_encryption" name="imap_encryption"
                                value="{{ setting('imap_encryption') }}" placeholder="ssl">
                        </div>
                        <div class="col-md-4">
                            <label for="imap_protocol" class="form-label fw-semibold">Protocolo</label>
                            <select class="form-select select2" id="imap_protocol" name="imap_protocol">
                                <option value="imap" {{ setting('imap_protocol') == 'imap' ? 'selected' : '' }}>IMAP</option>
                                <option value="pop3" {{ setting('imap_protocol') == 'pop3' ? 'selected' : '' }}>POP3</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="imap_username" class="form-label fw-semibold">Usuario IMAP</label>
                            <input type="text" class="form-control" id="imap_username" name="imap_username"
                                value="{{ setting('imap_username') }}" placeholder="correo@dominio.com">
                        </div>
                        <div class="col-md-4">
                            <label for="imap_password" class="form-label fw-semibold">Contraseña IMAP</label>
                            <input type="password" class="form-control" id="imap_password" name="imap_password"
                                placeholder="{{ setting('imap_password') ? '••••••••' : 'Ingresar contraseña' }}">
                        </div>
                    </div>
                </div>

                </div>{{-- /#imapFields --}}

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Guardar
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Ejemplo con Mailjet</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Host</span>
                        <strong><code>in-v3.mailjet.com</code></strong>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Puerto</span>
                        <strong>587</strong>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cifrado</span>
                        <strong>TLS</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Usuario / Contraseña</span>
                        <strong>API Key / Secret Key</strong>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Sobre IMAP</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">Con IMAP activo, el sistema revisa periódicamente el buzón configurado y convierte los correos recibidos en órdenes o matrículas automáticamente, según las reglas de <a href="{{ route('manager.mails.index') }}">correos entrantes</a>.</p>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/emails/setting.js') }}"></script>
@endpush
