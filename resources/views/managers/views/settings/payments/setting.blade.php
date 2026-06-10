@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">

            <form id="formPayments" enctype="multipart/form-data" role="form" onSubmit="return false">
                {{ csrf_field() }}

                {{-- Wompi --}}
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-1">
                        <h5 class="mb-0">Pasarela de pago — Wompi</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-2">
                        Configura las credenciales de Wompi para procesar pagos.
                        Las claves de <strong>sandbox</strong> tienen prefijo <code>pub_test_</code> / <code>test_integrity_</code>.
                        Las claves de <strong>producción</strong> tienen prefijo <code>pub_prod_</code> / <code>prod_integrity_</code>.
                        Obtén tus credenciales en
                        <a href="https://dashboard.wompi.co" target="_blank">dashboard.wompi.co</a>
                        → Desarrolladores → Llaves.
                    </p>

                    <div class="row">

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Llave pública <small class="text-muted">(Public Key)</small></label>
                                <input type="text" class="form-control" id="wompi_public_key" name="wompi_public_key"
                                    value="{{ setting('wompi_public_key') }}"
                                    placeholder="pub_test_XXXXXXXXXXXXXXXX  o  pub_prod_XXXXXXXXXXXXXXXX">
                                <small class="form-text text-muted">Sandbox: prefijo <code>pub_test_</code> — Producción: prefijo <code>pub_prod_</code></small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Secreto de integridad <small class="text-muted">(Integrity Secret)</small></label>
                                <input type="password" class="form-control" id="wompi_integrity_secret" name="wompi_integrity_secret"
                                    value="{{ setting('wompi_integrity_secret') }}"
                                    placeholder="test_integrity_XXXXXXXX  o  prod_integrity_XXXXXXXX">
                                <small class="form-text text-muted">Usado para firmar y verificar las transacciones del widget.</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Secreto de eventos <small class="text-muted">(Events Secret)</small></label>
                                <input type="password" class="form-control" id="wompi_events_secret" name="wompi_events_secret"
                                    value="{{ setting('wompi_events_secret') }}"
                                    placeholder="test_events_XXXXXXXX  o  prod_events_XXXXXXXX">
                                <small class="form-text text-muted">Usado para verificar la firma de los webhooks entrantes de Wompi.</small>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Sandbox toggle --}}
                <div class="card-body border-top">
                    <div class="row align-items-center">
                        <div class="col-sm-11">
                            <label class="control-label col-form-label">Modo sandbox (pruebas)</label>
                            <p class="card-subtitle mb-0 mt-0">
                                Activa para usar el entorno de pruebas de Wompi. Desactiva solo cuando uses credenciales de producción reales.
                            </p>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex align-items">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wompi_sandbox" id="wompi_sandbox"
                                    @if(setting('wompi_sandbox') === 'true') checked @endif />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Webhook URL informativa --}}
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-1">
                        <h5 class="mb-0">URL del Webhook</h5>
                    </div>
                    <p class="card-subtitle mb-2 mt-2">
                        Registra esta URL en tu dashboard de Wompi (Desarrolladores → Eventos) para recibir confirmaciones server-to-server:
                    </p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="webhookUrl"
                            value="{{ route('payments.wompi.webhook') }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyWebhook">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>

                {{-- Botón guardar --}}
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" id="btnSavePayments">
                        <i class="fas fa-save mr-1"></i> Guardar configuración
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('#formPayments').submit(function () {

        $.ajax({
            type: 'POST',
            url: '{{ route('manager.settings.payments.update') }}',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                toastr.error('Error al guardar la configuración');
            }
        });
    });

    $('#copyWebhook').on('click', function () {
        var url = $('#webhookUrl').val();
        navigator.clipboard.writeText(url).then(function () {
            toastr.info('URL copiada al portapapeles');
        });
    });

});
</script>
@endpush
