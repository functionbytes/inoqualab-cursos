@extends('layouts.managers')

@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna principal --}}
    <div class="col-lg-8">
        <form id="formNewsletter" onsubmit="return false">
            @csrf

            <div class="card">

                {{-- Suscripciones --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Suscripciones</h6>
                    <p class="text-muted mb-3">Controla si el formulario público de newsletter acepta nuevas suscripciones.</p>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="newsletter_enabled" id="newsletter_enabled"
                               @if(setting('newsletter_enabled') !== '0') checked @endif>
                        <label class="form-check-label fw-semibold" for="newsletter_enabled">
                            Newsletter activo
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">Cuando está desactivado, el formulario de suscripción no acepta nuevas altas.</small>
                </div>

                <hr class="my-0">

                {{-- Doble opt-in --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Doble opt-in</h6>
                    <p class="text-muted mb-3">El suscriptor debe confirmar su correo antes de quedar activo en la lista.</p>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="newsletter_double_optin" id="newsletter_double_optin"
                               @if(setting('newsletter_double_optin') == 1) checked @endif>
                        <label class="form-check-label fw-semibold" for="newsletter_double_optin">
                            Habilitar doble opt-in
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">Cuando está activo, se envía un email de verificación antes de activar la suscripción.</small>
                </div>

                <hr class="my-0">

                {{-- Notificaciones al admin --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Notificaciones</h6>
                    <p class="text-muted mb-3">Recibe un email cada vez que alguien se suscribe al newsletter.</p>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="newsletter_email_notifications" id="newsletter_email_notifications"
                               @if(setting('newsletter_email_notifications') == 1) checked @endif>
                        <label class="form-check-label fw-semibold" for="newsletter_email_notifications">
                            Notificar al administrador en cada nueva suscripción
                        </label>
                    </div>

                    <div id="notification-email-fields" @if(setting('newsletter_email_notifications') != 1) class="d-none" @endif>
                        <div class="mt-3" style="max-width:360px">
                            <label for="newsletter_notification_email" class="form-label fw-semibold">
                                Correo de destino
                            </label>
                            <input type="email" class="form-control" id="newsletter_notification_email"
                                   name="newsletter_notification_email"
                                   value="{{ setting('newsletter_notification_email') }}"
                                   placeholder="{{ config('mail.from.address') }}">
                            <small class="text-muted d-block mt-1">
                                Si se deja vacío se usa <strong>{{ config('mail.from.address') }}</strong>.
                            </small>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Popup --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Popup del boletín</h6>
                    <p class="text-muted mb-3">Muestra un popup invitando a los visitantes a suscribirse.</p>

                    <div class="form-check form-switch mb-2" id="popupToggleWrapper">
                        <input class="form-check-input" type="checkbox" name="newsletter_popup_enabled" id="newsletter_popup_enabled"
                               @if(setting('newsletter_popup_enabled') !== '0') checked @endif>
                        <label class="form-check-label fw-semibold" for="newsletter_popup_enabled">
                            Habilitar popup del boletín
                        </label>
                    </div>

                    <div id="popup-fields" @if(setting('newsletter_popup_enabled') === '0') class="d-none" @endif>
                        <div class="mb-0 mt-3" >
                            <label for="newsletter_popup_delay" class="form-label fw-semibold">
                                Retraso antes de mostrar <span class="text-muted fw-normal">(segundos)</span>
                            </label>
                            <input type="number" class="form-control" id="newsletter_popup_delay" name="newsletter_popup_delay"
                                   min="0" max="60" value="{{ setting('newsletter_popup_delay') ?: 2 }}">
                            <small class="text-muted d-block mt-1">0 = se muestra de inmediato. Recomendado: 2–5 segundos.</small>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Mailjet --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Integración Mailjet</h6>
                    <p class="text-muted mb-3">Sincroniza los suscriptores con una lista de Mailjet para gestionar envíos de email.</p>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="newsletter_mailjet_enabled" id="newsletter_mailjet_enabled"
                               @if(setting('newsletter_mailjet_enabled') == 1) checked @endif>
                        <label class="form-check-label fw-semibold" for="newsletter_mailjet_enabled">
                            Habilitar integración con Mailjet
                        </label>
                    </div>

                    <div id="mailjet-fields" @if(setting('newsletter_mailjet_enabled') != 1) class="d-none" @endif>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_mailjet_api_key" class="form-label fw-semibold">Clave API</label>
                                    <input type="text" class="form-control" id="newsletter_mailjet_api_key"
                                           name="newsletter_mailjet_api_key"
                                           value="{{ setting('newsletter_mailjet_api_key') }}"
                                           placeholder="Ej: a1b2c3d4e5f6...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_mailjet_api_secret" class="form-label fw-semibold">API Secret</label>
                                    <input type="password" class="form-control" id="newsletter_mailjet_api_secret"
                                           name="newsletter_mailjet_api_secret"
                                           value="{{ setting('newsletter_mailjet_api_secret') }}"
                                           placeholder="••••••••">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="newsletter_mailjet_list_id" class="form-label fw-semibold">ID de lista de contactos</label>
                                    <input type="text" class="form-control" id="newsletter_mailjet_list_id"
                                           name="newsletter_mailjet_list_id"
                                           value="{{ setting('newsletter_mailjet_list_id') }}"
                                           placeholder="Ej: 123456">
                                    <small class="text-muted d-block mt-1">Mailjet → Contactos → Listas.</small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 py-2 mb-0">
                            <small>Las credenciales se almacenan en la base de datos. Usa variables de entorno en producción.</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Guardar configuración
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha --}}
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-1">Suscriptores</h6>
                <p class="text-muted mb-3">Gestiona la lista de personas suscritas al boletín.</p>
                <a href="{{ route('manager.newsletter.index') }}" class="btn btn-primary w-100">
                    Ver suscriptores
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Cómo configurar Mailjet</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Clave API y Secret</h6>
                <ol class="text-muted ps-3 mb-3">
                    <li class="mb-1">Inicia sesión en <strong>Mailjet</strong></li>
                    <li class="mb-1">Ve a <strong>Account Settings → API Keys</strong></li>
                    <li>Copia la <strong>API Key</strong> y el <strong>Secret Key</strong></li>
                </ol>

                <hr class="my-3">

                <h6 class="fw-semibold mb-2">ID de lista de contactos</h6>
                <ol class="text-muted ps-3 mb-0">
                    <li class="mb-1">Ve a <strong>Contacts → Contact Lists</strong></li>
                    <li class="mb-1">Crea o selecciona una lista existente</li>
                    <li>Copia el <strong>ID numérico</strong> de la lista</li>
                </ol>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Show/hide condicional ─────────────────────────────────────────────────
    document.getElementById('newsletter_email_notifications')?.addEventListener('change', function () {
        document.getElementById('notification-email-fields').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('newsletter_popup_enabled')?.addEventListener('change', function () {
        document.getElementById('popup-fields').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('newsletter_mailjet_enabled')?.addEventListener('change', function () {
        document.getElementById('mailjet-fields').classList.toggle('d-none', !this.checked);
    });

    // ── Guardar via AJAX ──────────────────────────────────────────────────────
    document.getElementById('formNewsletter').addEventListener('submit', function () {
        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('newsletter_enabled',              document.getElementById('newsletter_enabled').checked ? 1 : 0);
        formData.append('newsletter_double_optin',          document.getElementById('newsletter_double_optin').checked ? 1 : 0);
        formData.append('newsletter_email_notifications',  document.getElementById('newsletter_email_notifications').checked ? 1 : 0);
        formData.append('newsletter_notification_email',   document.getElementById('newsletter_notification_email').value);
        formData.append('newsletter_popup_enabled',        document.getElementById('newsletter_popup_enabled').checked ? 1 : 0);
        formData.append('newsletter_popup_delay',          document.getElementById('newsletter_popup_delay').value);
        formData.append('newsletter_mailjet_enabled',      document.getElementById('newsletter_mailjet_enabled').checked ? 1 : 0);
        formData.append('newsletter_mailjet_api_key',      document.getElementById('newsletter_mailjet_api_key').value);
        formData.append('newsletter_mailjet_api_secret',   document.getElementById('newsletter_mailjet_api_secret').value);
        formData.append('newsletter_mailjet_list_id',      document.getElementById('newsletter_mailjet_list_id').value);

        $.ajax({
            url: '{{ route('manager.settings.newsletter.update') }}',
            type: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, 'Operación exitosa', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Error al guardar.';
                toastr.error(msg, 'Error', { closeButton: true, positionClass: 'toast-bottom-right' });
            }
        });
    });

})();
</script>
@endpush
