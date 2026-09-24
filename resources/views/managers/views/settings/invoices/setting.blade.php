@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Configuración de facturación'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formInvoices" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.settings.invoices.update"),
                      "dashboard" => route("manager.dashboard"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Numeración</h6>
                        <p class="text-muted small mb-0">
                            Define el consecutivo y la periodicidad con la que se generan las facturas.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="invoice_default" class="form-label fw-semibold">Consecutivo de facturación</label>
                                <input type="text" class="form-control" id="invoice_default" name="invoice_default" value="{{ setting('invoice_default') }}">
                                <small class="text-muted d-block mt-1">Prefijo que antecede al número de cada factura nueva. Cambiarlo no afecta las facturas ya generadas.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_days" class="form-label fw-semibold">Tiempo de facturación (días)</label>
                                <input type="text" class="form-control" id="invoice_days" name="invoice_days" value="{{ setting('invoice_days') }}">
                                <small class="text-muted d-block mt-1">Cada cuántos días se factura al cliente.</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Notificaciones</h6>
                        <p class="text-muted mb-3">Aviso automático al equipo de contabilidad cuando se genera una factura.</p>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="invoices_notification_email_enable" id="invoices_notification_email_enable" @if(setting('invoices_notification_email_enable') == 'true') checked @endif>
                            <label class="form-check-label fw-semibold" for="invoices_notification_email_enable">Habilitar correo CC a contabilidad</label>
                        </div>
                        <small class="text-muted d-block mt-1">Si se habilita, cada factura generada envía automáticamente un correo con el reporte a todos los usuarios con rol contabilidad.</small>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Diseño de órdenes y facturas</h6>
                        <p class="text-muted small mb-3">
                            Cómo se ven el detalle de una orden, la factura y su reparto por empresa en el panel.
                            En cada una de esas pantallas puedes cambiar de diseño en el momento desde la cabecera, sin tocar este ajuste.
                        </p>
                        @php $currentDesign = setting('panel_documents_design', 'a'); @endphp
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="doc-design-option" for="design_original">
                                    <input type="radio" class="form-check-input" name="panel_documents_design" id="design_original" value="original" @checked($currentDesign === 'original')>
                                    <span class="doc-design-body">
                                        <span class="doc-design-name">Actual @if($currentDesign === 'original') <span class="badge bg-success-subtle text-success ms-1">En uso</span>@endif</span>
                                        <span class="doc-design-desc">La vista anterior, sin cambios.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="doc-design-option" for="design_a">
                                    <input type="radio" class="form-check-input" name="panel_documents_design" id="design_a" value="a" @checked($currentDesign === 'a')>
                                    <span class="doc-design-body">
                                        <span class="doc-design-name">Comprobante @if($currentDesign === 'a') <span class="badge bg-success-subtle text-success ms-1">En uso</span>@endif</span>
                                        <span class="doc-design-desc">Hoja de papel centrada con sello de estado. La más parecida a un documento impreso.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="doc-design-option" for="design_b">
                                    <input type="radio" class="form-check-input" name="panel_documents_design" id="design_b" value="b" @checked($currentDesign === 'b')>
                                    <span class="doc-design-body">
                                        <span class="doc-design-name">Mesa de trabajo @if($currentDesign === 'b') <span class="badge bg-success-subtle text-success ms-1">En uso</span>@endif</span>
                                        <span class="doc-design-desc">Contenido a la izquierda; total, estado e historial con fechas a la derecha.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="doc-design-option" for="design_c">
                                    <input type="radio" class="form-check-input" name="panel_documents_design" id="design_c" value="c" @checked($currentDesign === 'c')>
                                    <span class="doc-design-body">
                                        <span class="doc-design-name">Ficha técnica @if($currentDesign === 'c') <span class="badge bg-success-subtle text-success ms-1">En uso</span>@endif</span>
                                        <span class="doc-design-desc">Franja oscura con cuadrícula de datos, como un informe de laboratorio.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

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

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre estos ajustes</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold mb-2">Consecutivo</h6>
                    <p class="text-muted mb-3">Se antepone al número interno de la factura para formar la referencia visible al cliente.</p>

                    <hr class="my-3">

                    <h6 class="fw-semibold mb-2">Correo a contabilidad</h6>
                    <p class="text-muted mb-0">El envío se procesa en segundo plano; si el correo no llega, revisa la cola <code>emails</code> del sistema.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/invoices/setting.css') }}?v={{ @filemtime(public_path('managers/css/views/settings/invoices/setting.css')) ?: '1' }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/settings/invoices/setting.js') }}"></script>
@endpush
