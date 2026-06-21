@extends('layouts.managers')

@section('title', 'IndexNow')

@section('content')


    <div class="row g-4">

        {{-- Card: Estado --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Estado</h5>
                    <p class="small mb-3 text-muted">IndexNow avisa a Bing, Yandex y Seznam al instante cuando publicas o actualizas contenido.</p>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Activado</span>
                            @if($enabled === '1')
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Clave</span>
                            @if($key)
                                <code class="small text-break">{{ $key }}</code>
                            @else
                                <span class="badge bg-warning text-dark">No configurada</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="text-muted text-nowrap">Archivo de verificacion</span>
                            @if($key && $key_url)
                                <a href="{{ $key_url }}" target="_blank" rel="noopener" class="small text-break text-end">
                                    {{ $key_url }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Host</span>
                            <code class="small">{{ $host ?: '—' }}</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Configuración --}}
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-2 border-bottom pb-2">Configuracion (.env / settings)</h6>
                    <p class="small text-muted mb-2">Agrega las siguientes variables en tu archivo <code>.env</code>:</p>
                    <pre class="small bg-light p-2 rounded mb-3" style="font-size: 0.72rem; white-space: pre-wrap;">SEO_INDEXNOW_ENABLED=true
SEO_INDEXNOW_KEY=tu_key_aqui</pre>
                    @if(! $key)
                        <div class="alert alert-warning small mb-3 py-2">
                            Falta <code>SEO_INDEXNOW_KEY</code> en <code>.env</code>. Genera una cadena alfanumérica de 8–128 caracteres y añádela.
                        </div>
                    @endif
                    <a href="{{ route('manager.settings.seo.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-cog me-1"></i> Configuracion SEO
                    </a>
                </div>
            </div>
        </div>

        {{-- Card: Enviar URLs manualmente --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header p-4 border-bottom">
                    <h5 class="mb-1 fw-bold">Enviar URLs manualmente</h5>
                    <p class="small mb-0 text-muted">
                        Una URL por línea. Deben compartir el host configurado:
                        <code>{{ $host ?: '—' }}</code>.
                    </p>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="urls-input" class="form-label fw-semibold">URLs</label>
                        <textarea
                            id="urls-input"
                            rows="10"
                            class="form-control font-monospace"
                            placeholder="https://{{ $host ?: 'tu-sitio.com' }}/mi-pagina&#10;https://{{ $host ?: 'tu-sitio.com' }}/otra-pagina"
                            @if($enabled !== '1') disabled @endif
                        ></textarea>
                        <div id="urls-error" class="invalid-feedback d-none"></div>
                    </div>

                    <button
                        type="button"
                        id="btn-submit-indexnow"
                        class="btn btn-primary w-100"
                        @if($enabled !== '1') disabled @endif
                    >
                        <i class="fas fa-paper-plane me-1"></i> Enviar a IndexNow
                    </button>

                    @if($enabled !== '1')
                        <div class="alert alert-warning small mt-3 mb-0 py-2">
                            IndexNow está desactivado. Configura <code>SEO_INDEXNOW_ENABLED=true</code> y una <code>SEO_INDEXNOW_KEY</code> válida en <code>.env</code>.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-2 border-bottom pb-2">Que es IndexNow?</h6>
                    <p class="text-muted small mb-2">
                        Protocolo abierto de Bing, Yandex y Seznam que permite notificar actualizaciones de contenido
                        sin esperar al rastreo periódico. Reduce el tiempo hasta la indexación de días a segundos.
                    </p>
                    <p class="text-muted small mb-0">
                        Especificacion:
                        <a href="https://www.indexnow.org/documentation" target="_blank" rel="noopener">
                            indexnow.org/documentation
                        </a>
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('#btn-submit-indexnow').on('click', function () {
        var rawText = $('#urls-input').val().trim();

        if (!rawText) {
            toastr.warning('Escribe al menos una URL antes de enviar.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var urls = rawText.split('\n')
            .map(function (u) { return u.trim(); })
            .filter(function (u) { return u.length > 0; });

        if (!urls.length) {
            toastr.warning('No se encontraron URLs válidas.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enviando...');

        $.ajax({
            url: '{{ route('manager.seo.indexnow.submit') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { urls: urls },
            success: function (response) {
                toastr.success(response.message ?? 'URLs enviadas correctamente.', 'Exito', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
                $('#urls-input').val('');
            },
            error: function (xhr) {
                var message = 'Error al enviar las URLs.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    message = errors[firstKey][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message, 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar a IndexNow');
            }
        });
    });

});
</script>
@endpush
