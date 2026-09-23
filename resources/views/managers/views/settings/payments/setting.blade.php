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

            @php
                $isSandbox = setting('wompi_sandbox') === 'true';
                // Compat: si aun no se guardo nada en la clave con sufijo
                // "_sandbox", precargar desde la clave legacy sin sufijo (la
                // unica que existia antes de separar sandbox/produccion) para
                // no mostrar el campo vacio y que parezca que se perdio la
                // credencial ya configurada. NUNCA se hace este fallback para
                // produccion: una llave legacy es casi siempre de pruebas
                // (prefijo pub_test_), precargarla como "produccion" seria
                // peligroso.
                $sandboxPublicKey = setting('wompi_public_key_sandbox') ?: setting('wompi_public_key');
                $sandboxHasIntegritySecret = (bool) (setting('wompi_integrity_secret_sandbox') ?: setting('wompi_integrity_secret'));
                $sandboxHasEventsSecret = (bool) (setting('wompi_events_secret_sandbox') ?: setting('wompi_events_secret'));
                $productionHasIntegritySecret = (bool) setting('wompi_integrity_secret_production');
                $productionHasEventsSecret = (bool) setting('wompi_events_secret_production');
            @endphp

            <div class="card">

                {{-- Entorno --}}
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Entorno</h6>
                    <p class="text-muted small mb-0">Activa el modo sandbox para usar el entorno de pruebas de Wompi. Desactívalo solo cuando uses credenciales de producción reales.</p>
                </div>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-11">
                            <label class="form-label fw-semibold mb-0" for="wompi_sandbox">Modo sandbox (pruebas)</label>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wompi_sandbox" id="wompi_sandbox"
                                    @checked($isSandbox) />
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Credenciales sandbox --}}
                <div class="card-body wompi-credentials-group {{ $isSandbox ? '' : 'd-none' }}" data-env="sandbox">
                    <h6 class="fw-bold text-dark mb-1">Credenciales de prueba (sandbox)</h6>
                    <p class="text-muted mb-3">Se usan mientras el modo sandbox está activo. Obtén tus credenciales de prueba en <a href="https://dashboard.wompi.co" target="_blank">dashboard.wompi.co</a> → Desarrolladores → Llaves.</p>

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="wompi_public_key_sandbox" class="form-label fw-semibold">Llave pública <span class="text-muted fw-normal">(Public Key)</span></label>
                            <input type="text" class="form-control" id="wompi_public_key_sandbox" name="wompi_public_key_sandbox"
                                value="{{ $sandboxPublicKey }}"
                                placeholder="pub_test_XXXXXXXXXXXXXXXX">
                            <small class="text-muted d-block mt-1">Prefijo <code>pub_test_</code></small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_integrity_secret_sandbox" class="form-label fw-semibold">Secreto de integridad <span class="text-muted fw-normal">(Integrity Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_integrity_secret_sandbox" name="wompi_integrity_secret_sandbox"
                                autocomplete="new-password"
                                placeholder="{{ $sandboxHasIntegritySecret ? '•••••••• (guardado — deja vacío para conservarlo)' : 'test_integrity_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para firmar y verificar las transacciones del widget. Deja el campo vacío para mantener el valor actual.</small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_events_secret_sandbox" class="form-label fw-semibold">Secreto de eventos <span class="text-muted fw-normal">(Events Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_events_secret_sandbox" name="wompi_events_secret_sandbox"
                                autocomplete="new-password"
                                placeholder="{{ $sandboxHasEventsSecret ? '•••••••• (guardado — deja vacío para conservarlo)' : 'test_events_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para verificar la firma de los webhooks entrantes de Wompi. Deja el campo vacío para mantener el valor actual.</small>
                        </div>

                    </div>
                </div>

                {{-- Credenciales producción --}}
                <div class="card-body wompi-credentials-group {{ $isSandbox ? 'd-none' : '' }}" data-env="production">
                    <h6 class="fw-bold text-dark mb-1">Credenciales de producción</h6>
                    <p class="text-muted mb-3">Se usan cuando el modo sandbox está desactivado. Obtén tus credenciales reales en <a href="https://dashboard.wompi.co" target="_blank">dashboard.wompi.co</a> → Desarrolladores → Llaves.</p>

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="wompi_public_key_production" class="form-label fw-semibold">Llave pública <span class="text-muted fw-normal">(Public Key)</span></label>
                            <input type="text" class="form-control" id="wompi_public_key_production" name="wompi_public_key_production"
                                value="{{ setting('wompi_public_key_production') }}"
                                placeholder="pub_prod_XXXXXXXXXXXXXXXX">
                            <small class="text-muted d-block mt-1">Prefijo <code>pub_prod_</code></small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_integrity_secret_production" class="form-label fw-semibold">Secreto de integridad <span class="text-muted fw-normal">(Integrity Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_integrity_secret_production" name="wompi_integrity_secret_production"
                                autocomplete="new-password"
                                placeholder="{{ $productionHasIntegritySecret ? '•••••••• (guardado — deja vacío para conservarlo)' : 'prod_integrity_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para firmar y verificar las transacciones del widget. Deja el campo vacío para mantener el valor actual.</small>
                        </div>

                        <div class="col-12">
                            <label for="wompi_events_secret_production" class="form-label fw-semibold">Secreto de eventos <span class="text-muted fw-normal">(Events Secret)</span></label>
                            <input type="password" class="form-control" id="wompi_events_secret_production" name="wompi_events_secret_production"
                                autocomplete="new-password"
                                placeholder="{{ $productionHasEventsSecret ? '•••••••• (guardado — deja vacío para conservarlo)' : 'prod_events_XXXXXXXX' }}">
                            <small class="text-muted d-block mt-1">Usado para verificar la firma de los webhooks entrantes de Wompi. Deja el campo vacío para mantener el valor actual.</small>
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
