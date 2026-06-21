@extends('layouts.managers')

@section('title', 'Nuevo reporte programado')

@section('content')


    <div class="row g-3">

        {{-- Columna del formulario --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <form id="scheduleForm"
                      action="{{ route('manager.settings.analytics.schedules.store') }}"
                      method="POST">
                    @csrf

                    <div class="card-header border-bottom p-3">
                        <h5 class="mb-0 fw-bold">Nuevo reporte programado</h5>
                        <span class="text-muted">Complete la informacion del reporte automatico.</span>
                    </div>

                    <div class="card-body">

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Informacion basica</h6>
                        <div class="row g-3 mb-4">

                            <div class="col-12">
                                <p class="text-muted mb-0">
                                    Configura el nombre, la frecuencia de envio y el formato del archivo adjunto que recibiran los destinatarios.
                                </p>
                            </div>

                            {{-- Nombre --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="ej: Reporte semanal de trafico">
                                <p class="text-muted mt-1 mb-0">Nombre descriptivo para identificar el reporte.</p>
                                @error('name')
                                    <span class="field-validation-error">
                                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            {{-- Frecuencia --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Frecuencia <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('frequency') is-invalid @enderror"
                                        name="frequency">
                                    <option value="">Seleccionar...</option>
                                    <option value="daily"
                                        {{ old('frequency') === 'daily' ? 'selected' : '' }}>
                                        Diario
                                    </option>
                                    <option value="weekly"
                                        {{ old('frequency') === 'weekly' ? 'selected' : '' }}>
                                        Semanal
                                    </option>
                                    <option value="monthly"
                                        {{ old('frequency') === 'monthly' ? 'selected' : '' }}>
                                        Mensual
                                    </option>
                                </select>
                                @error('frequency')
                                    <span class="field-validation-error">
                                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            {{-- Formato --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Formato <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('format') is-invalid @enderror"
                                        name="format">
                                    <option value="">Seleccionar...</option>
                                    <option value="pdf"
                                        {{ old('format') === 'pdf' ? 'selected' : '' }}>
                                        PDF
                                    </option>
                                    <option value="excel"
                                        {{ old('format') === 'excel' ? 'selected' : '' }}>
                                        Excel
                                    </option>
                                    <option value="csv"
                                        {{ old('format') === 'csv' ? 'selected' : '' }}>
                                        CSV
                                    </option>
                                </select>
                                @error('format')
                                    <span class="field-validation-error">
                                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Destinatario</h6>
                        <div class="row g-3 mb-4">

                            <div class="col-12">
                                <p class="text-muted mb-0">
                                    Indica la direccion de correo que recibira el reporte en cada envio programado.
                                </p>
                            </div>

                            {{-- Email --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="ejemplo@dominio.com">
                                <p class="text-muted mt-1 mb-0">El reporte se enviara a esta direccion.</p>
                                @error('email')
                                    <span class="field-validation-error">
                                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Configuracion</h6>
                        <div class="row g-3">

                            <div class="col-12">
                                <p class="text-muted mb-3">
                                    Controla si el reporte se ejecuta automaticamente. Puedes cambiar el estado en cualquier momento.
                                </p>
                                <label class="form-label fw-semibold" for="isActive">Estado del reporte</label>
                                <select class="form-select select2 @error('is_active') is-invalid @enderror"
                                        id="isActive" name="is_active">
                                    <option value="1" {{ old('is_active', '1') != '0' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active', '1') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <p class="text-muted mt-1 mb-0">
                                    Los reportes <strong>activos</strong> se envian automaticamente segun la frecuencia configurada.
                                    Los reportes <strong>inactivos</strong> quedan pausados y no generan envios.
                                </p>
                                @error('is_active')
                                    <span class="field-validation-error">
                                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Guardar reporte
                        </button>
                        <a href="{{ route('manager.settings.analytics.schedules.index') }}"
                           class="btn btn-light w-100">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Panel informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        Frecuencias disponibles
                    </h6>
                    <dl class="mb-0">
                        <dt class="fw-semibold">Diario</dt>
                        <dd class="text-muted mb-2">Se envia cada dia a las 8:00 AM.</dd>
                        <dt class="fw-semibold">Semanal</dt>
                        <dd class="text-muted mb-2">Se envia cada lunes a las 8:00 AM con datos de la semana anterior.</dd>
                        <dt class="fw-semibold">Mensual</dt>
                        <dd class="text-muted mb-0">Se envia el primer dia del mes con datos del mes anterior.</dd>
                    </dl>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        Formatos de exportacion
                    </h6>
                    <dl class="mb-0">
                        <dt class="fw-semibold">PDF</dt>
                        <dd class="text-muted mb-2">Reporte visual con graficas. Ideal para presentaciones.</dd>
                        <dt class="fw-semibold">Excel</dt>
                        <dd class="text-muted mb-2">Datos en hojas de calculo. Ideal para analisis.</dd>
                        <dt class="fw-semibold">CSV</dt>
                        <dd class="text-muted mb-0">Datos en texto plano. Compatible con cualquier herramienta.</dd>
                    </dl>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        Campos requeridos
                    </h6>
                    <p class="card-text text-muted mb-0">
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ width: '100%' });

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
});
</script>
@endpush
