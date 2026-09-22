@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Configuración de pagos'])
@endsection
@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
        <form id="formPayments" enctype="multipart/form-data" role="form"
              data-update-url="{{ route('manager.settings.payments.update') }}">
            {{ csrf_field() }}

            <div class="card">

                {{-- Wompi --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Pasarela de pago — Wompi</h6>
                    <p class="text-muted mb-3">Configura las credenciales de Wompi para procesar pagos. Obtén tus credenciales en <a href="https://dashboard.wompi.co" target="_blank">dashboard.wompi.co</a> → Desarrolladores → Llaves.</p>

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="wompi_public_key" class="form-label fw-semibold">Llave pública <span class="text-muted fw-normal">(Public Key)</span></label>
                            <input type="text" class="form-control" id="wompi_public_key" name="wompi_public_key"
                                value="{{ setting('wompi_public_key') }}"
                                placeholder="pub_test_XXXXXXXXXXXXXXXX  o  pub_prod_XXXXXXXXXXXXXXXX">
                            <small class="text-muted d-block mt-1">Sandbox: prefijo <code>pub_test_</code> — Producción: prefijo <code>pub_prod_</code></small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_integrity_secret" class="form-label fw-semibold">Secreto de integridad <span class="text-muted fw-normal">(Integrity Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_integrity_secret" name="wompi_integrity_secret"
                                autocomplete="new-password"
                                placeholder="{{ setting('wompi_integrity_secret') ? '•••••••• (guardado — deja vacío para conservarlo)' : 'test_integrity_XXXXXXXX  o  prod_integrity_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para firmar y verificar las transacciones del widget. Deja el campo vacío para mantener el valor actual.</small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_events_secret" class="form-label fw-semibold">Secreto de eventos <span class="text-muted fw-normal">(Events Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_events_secret" name="wompi_events_secret"
                                autocomplete="new-password"
                                placeholder="{{ setting('wompi_events_secret') ? '•••••••• (guardado — deja vacío para conservarlo)' : 'test_events_XXXXXXXX  o  prod_events_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para verificar la firma de los webhooks entrantes de Wompi. Deja el campo vacío para mantener el valor actual.</small>
                        </div>

                    </div>
                </div>

                <hr class="my-0">

                {{-- Sandbox toggle --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Entorno</h6>
                    <p class="text-muted mb-3">Activa el modo sandbox para usar el entorno de pruebas de Wompi. Desactívalo solo cuando uses credenciales de producción reales.</p>

                    <div class="row g-3 align-items-center">
                        <div class="col-sm-11">
                            <label class="form-label fw-semibold mb-0" for="wompi_sandbox">Modo sandbox (pruebas)</label>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wompi_sandbox" id="wompi_sandbox"
                                    @if(setting('wompi_sandbox') === 'true') checked @endif />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSavePayments">
                        Guardar configuración
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">URL del Webhook</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Regístrala en tu dashboard de Wompi (Desarrolladores → Eventos) para recibir confirmaciones server-to-server:</p>
                <div class="input-group">
                    <input type="text" class="form-control" id="webhookUrl"
                        value="{{ route('payments.wompi.webhook') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyWebhook">
                        Copiar
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/payments/setting.js') }}"></script>
@endpush
