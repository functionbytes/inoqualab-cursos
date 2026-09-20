@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">

            <form id="formEmails" data-update-url="{{ route('manager.settings.emails.update') }}">
                @csrf

                {{-- ═══════════════ SMTP / ENVÍO DE CORREOS ═══════════════ --}}
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-1">
                        <h5 class="mb-0">Configuración SMTP (envío de correos)</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-2">
                        Credenciales del servidor de correo saliente. Compatible con Mailjet, Mailgun, SendGrid, Gmail u otro servidor SMTP.
                        <br>
                        <strong>Mailjet:</strong> Host <code>in-v3.mailjet.com</code> · Puerto <code>587</code> · Cifrado <code>TLS</code> · Usuario = API Key · Contraseña = Secret Key.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Host SMTP</label>
                                <input type="text" class="form-control" id="mail_host" name="mail_host"
                                    value="{{ setting('mail_host') }}" placeholder="in-v3.mailjet.com">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Puerto</label>
                                <input type="number" class="form-control" id="mail_port" name="mail_port"
                                    value="{{ setting('mail_port') ?: 587 }}" placeholder="587">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Cifrado</label>
                                <select class="form-select" id="mail_encryption" name="mail_encryption">
                                    <option value="tls"      {{ setting('mail_encryption') == 'tls'      ? 'selected' : '' }}>TLS (recomendado)</option>
                                    <option value="ssl"      {{ setting('mail_encryption') == 'ssl'      ? 'selected' : '' }}>SSL</option>
                                    <option value="starttls" {{ setting('mail_encryption') == 'starttls' ? 'selected' : '' }}>STARTTLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Usuario / API Key</label>
                                <input type="text" class="form-control" id="mail_username" name="mail_username"
                                    value="{{ setting('mail_username') }}" placeholder="API Key de Mailjet">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Contraseña / Secret Key</label>
                                <input type="password" class="form-control" id="mail_password" name="mail_password"
                                    placeholder="Dejar vacío para no cambiar">
                                <p class="text-muted">Dejar en blanco si no deseas cambiar la contraseña actual.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Correo remitente (From)</label>
                                <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                                    value="{{ setting('mail_from_address') }}" placeholder="no-reply@tudominio.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Nombre remitente</label>
                                <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                                    value="{{ setting('mail_from_name') }}" placeholder="Nombre de tu plataforma">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════ IMAP / RECEPCIÓN DE CORREOS ═══════════════ --}}
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-1">
                        <h5 class="mb-0">Configuración IMAP (recepción de correos)</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-2">
                        Conexión al buzón de entrada. Usado para procesar correos entrantes y crear órdenes/matrículas automáticamente.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Host IMAP</label>
                                <input type="text" class="form-control" id="imap_host" name="imap_host"
                                    value="{{ setting('imap_host') }}" placeholder="imap.gmail.com">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Puerto</label>
                                <input type="text" class="form-control" id="imap_port" name="imap_port"
                                    value="{{ setting('imap_port') }}" placeholder="993">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Cifrado</label>
                                <input type="text" class="form-control" id="imap_encryption" name="imap_encryption"
                                    value="{{ setting('imap_encryption') }}" placeholder="ssl">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Protocolo</label>
                                <select class="form-select" id="imap_protocol" name="imap_protocol">
                                    <option value="imap" {{ setting('imap_protocol') == 'imap' ? 'selected' : '' }}>IMAP</option>
                                    <option value="pop3" {{ setting('imap_protocol') == 'pop3' ? 'selected' : '' }}>POP3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Usuario IMAP</label>
                                <input type="text" class="form-control" id="imap_username" name="imap_username"
                                    value="{{ setting('imap_username') }}" placeholder="correo@dominio.com">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Contraseña IMAP</label>
                                <input type="password" class="form-control" id="imap_password" name="imap_password"
                                    placeholder="{{ setting('imap_password') ? '••••••••' : 'Ingresar contraseña' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center mt-2">
                        <div class="col-sm-11">
                            <label class="control-label col-form-label">IMAP activo</label>
                            <p class="card-subtitle mb-0 mt-0">Activar el procesamiento de correos entrantes para crear órdenes y matrículas.</p>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="imap_status" id="imap_status"
                                    @if(setting('imap_status') == 'true') checked @endif>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body border-top">
                    <div class="col-12">
                        <button type="submit" class="btn btn-info px-4 waves-effect waves-light w-100">
                            Guardar
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/emails/setting.js') }}"></script>
@endpush
