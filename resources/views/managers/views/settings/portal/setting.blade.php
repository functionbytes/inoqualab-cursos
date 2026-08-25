@extends('layouts.managers')

@section('title', 'Portal del alumno')

@push('css')
<style>
    .pv-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .pv-opt { position: relative; }
    .pv-opt input { position: absolute; opacity: 0; pointer-events: none; }
    .pv-opt label {
        display: block; cursor: pointer; border: 2px solid #e7ecf1; border-radius: 14px;
        padding: 16px; background: #fff; transition: border-color .15s ease, box-shadow .15s ease;
        height: 100%;
    }
    .pv-opt label:hover { border-color: #b9d9ec; }
    .pv-opt input:checked + label { border-color: #008bcd; box-shadow: 0 6px 22px rgba(0,139,205,.16); }
    .pv-opt input:focus-visible + label { outline: 2px solid #008bcd; outline-offset: 2px; }
    .pv-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .pv-name { font-size: 14px; font-weight: 700; color: #1b2a3a; }
    .pv-tag { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6a7888; background: #f0f4f8; padding: 3px 9px; border-radius: 20px; }
    .pv-opt input:checked + label .pv-tag { background: #e4f2fb; color: #006fa3; }
    .pv-desc { font-size: 12.5px; line-height: 1.6; color: #6a7888; margin: 12px 0 0; }
    .pv-sec { margin-bottom: 30px; }
    .pv-sec-title { font-size: 15px; font-weight: 600; color: #1b2a3a; margin-bottom: 4px; }
    .pv-sec-note { font-size: 12.5px; color: #6a7888; margin-bottom: 14px; }

    /* Miniatura esquemática, sin estilos inline */
    .pv-shot { background: #f3f6f9; border: 1px solid #e7ecf1; border-radius: 8px; padding: 8px; display: flex; gap: 6px; height: 116px; }
    .pv-shot.is-stacked { flex-direction: column; }
    .pv-shot .bar { background: #0d1b2a; border-radius: 3px; }
    .pv-shot .blk { background: #fff; border: 1px solid #e2e8ee; border-radius: 3px; }
    .pv-shot .acc { background: #008bcd; border-radius: 3px; }
    .pv-col { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 0; }
    .pv-row { display: flex; gap: 6px; }
    .pv-topbar { height: 9px; }
    .pv-sidebar { width: 26px; }
    .pv-hero { height: 38px; }
    .pv-fill { flex: 1; }
    .pv-strip { height: 16px; }
    .pv-lines { height: 13px; }
    .pv-chips { height: 12px; }
    .pv-chips .acc, .pv-chips .blk { width: 34px; }
    .pv-aside { width: 40px; opacity: .85; }
    .pv-panel { width: 74px; }
</style>
@endpush

@php
    /**
     * Cada entrada define un ajuste con sus dos opciones. La miniatura se
     * compone con las clases .pv-* de arriba para no repetir markup.
     */
    $secciones = [
        [
            'key' => 'customers_nav_layout',
            'title' => 'Menú de navegación',
            'note' => 'Vale para todo el portal: si cambiara por pantalla, el menú saltaría de arriba al lateral al navegar.',
            'options' => [
                ['value' => 'horizontal', 'name' => 'Barra superior', 'tag' => 'Actual', 'shot' => 'nav-h',
                 'desc' => 'Las secciones se muestran en una barra horizontal sobre el contenido.'],
                ['value' => 'vertical', 'name' => 'Barra lateral', 'tag' => 'Alternativa', 'shot' => 'nav-v',
                 'desc' => 'Menú fijo a la izquierda. El contenido gana ancho y las secciones quedan siempre visibles.'],
            ],
            'default' => 'horizontal',
        ],
        [
            'key' => 'customers_dashboard_variant',
            'title' => 'Panel de inicio',
            'options' => [
                ['value' => 'a', 'name' => 'Clásico', 'tag' => 'Opción A', 'shot' => 'dash-a',
                 'desc' => 'Saludo, curso para retomar, cuatro indicadores y una columna lateral con la ruta y los certificados.'],
                ['value' => 'b', 'name' => 'Ruta formativa', 'tag' => 'Opción B', 'shot' => 'dash-b',
                 'desc' => 'La sesión de hoy ocupa el bloque principal y los módulos se presentan como una ruta con su estado.'],
            ],
        ],
        [
            'key' => 'customers_courses_variant',
            'title' => 'Mis cursos',
            'options' => [
                ['value' => 'a', 'name' => 'Rejilla de tarjetas', 'tag' => 'Opción A', 'shot' => 'grid',
                 'desc' => 'Una tarjeta por curso con portada, progreso y el botón que corresponda a su estado. Filtros con recuento.'],
                ['value' => 'b', 'name' => 'Expediente en lista', 'tag' => 'Opción B', 'shot' => 'list-aside',
                 'desc' => 'Todos los cursos en filas compactas, con panel lateral de estado global. Útil con muchas inscripciones.'],
            ],
        ],
        [
            'key' => 'aula_version',
            'title' => 'Aula / Lección',
            'note' => 'Dónde se coloca el temario mientras el alumno ve la clase.',
            'options' => [
                ['value' => '1', 'name' => 'Temario a la derecha', 'tag' => 'Opción A', 'shot' => 'split',
                 'desc' => 'El contenido de la clase a la izquierda y el temario del curso en una columna a la derecha.'],
                ['value' => '2', 'name' => 'Temario a la izquierda', 'tag' => 'Opción B', 'shot' => 'list-aside',
                 'desc' => 'Temario fijo a la izquierda, tipo campus, y el contenido de la clase ocupando el resto.'],
            ],
            'default' => '1',
        ],
        [
            'key' => 'customers_certificates_variant',
            'title' => 'Certificados',
            'options' => [
                ['value' => 'a', 'name' => 'Galería', 'tag' => 'Opción A', 'shot' => 'grid',
                 'desc' => 'Cada certificado como una tarjeta con miniatura del diploma, vigencia y descarga directa.'],
                ['value' => 'b', 'name' => 'Lista y vista previa', 'tag' => 'Opción B', 'shot' => 'split',
                 'desc' => 'Lista a la izquierda y previsualización grande del diploma a la derecha, sin abrir el PDF.'],
            ],
        ],
        [
            'key' => 'customers_orders_variant',
            'title' => 'Pedidos',
            'options' => [
                ['value' => 'a', 'name' => 'Tabla', 'tag' => 'Opción A', 'shot' => 'rows',
                 'desc' => 'Una fila por pedido, mostrando qué contiene, su importe y el botón de pago cuando aplica.'],
                ['value' => 'b', 'name' => 'Compras agrupadas', 'tag' => 'Opción B', 'shot' => 'list-aside',
                 'desc' => 'Cada compra como una ficha con sus cursos y su factura, agrupadas por mes.'],
            ],
        ],
        [
            'key' => 'customers_documents_variant',
            'title' => 'Documentos',
            'options' => [
                ['value' => 'a', 'name' => 'Lista', 'tag' => 'Opción A', 'shot' => 'rows',
                 'desc' => 'Lista con el tipo de archivo, su peso y la descarga a un clic.'],
                ['value' => 'b', 'name' => 'Biblioteca', 'tag' => 'Opción B', 'shot' => 'tree-grid',
                 'desc' => 'Cuadrícula con vista previa de cada archivo y un panel lateral por tipo de documento.'],
            ],
        ],
        [
            'key' => 'customers_settings_variant',
            'title' => 'Configuración de la cuenta',
            'options' => [
                ['value' => 'a', 'name' => 'Página completa', 'tag' => 'Opción A', 'shot' => 'form',
                 'desc' => 'Todas las secciones en una página, con un índice arriba para saltar entre ellas.'],
                ['value' => 'b', 'name' => 'Carnet y pestañas', 'tag' => 'Opción B', 'shot' => 'tabs',
                 'desc' => 'Cabecera con los datos del alumno y el porcentaje de perfil completo; el resto en pestañas.'],
            ],
        ],
        [
            'key' => 'customers_notifications_variant',
            'title' => 'Notificaciones',
            'options' => [
                ['value' => 'a', 'name' => 'Agrupadas por fecha', 'tag' => 'Opción A', 'shot' => 'rows',
                 'desc' => 'Lista cronológica con las no leídas destacadas y botón para marcarlas todas.'],
                ['value' => 'b', 'name' => 'Bandeja', 'tag' => 'Opción B', 'shot' => 'split',
                 'desc' => 'Dos paneles: la lista a la izquierda y el aviso completo a la derecha, como un buzón.'],
            ],
        ],
    ];
@endphp

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <form id="formPortal" role="form" onSubmit="return false">
                {{ csrf_field() }}

                <div class="card-body border-top">

                    <h5 class="mb-1">Portal del alumno</h5>
                    <p class="card-subtitle mb-4 mt-0">
                        Elige qué diseño ve el alumno en cada pantalla. El cambio se aplica de inmediato para todos los alumnos.
                    </p>

                    @foreach($secciones as $seccion)
                        @php $actual = setting($seccion['key'], $seccion['default'] ?? 'a'); @endphp

                        <div class="pv-sec">
                            <div class="pv-sec-title">{{ $seccion['title'] }}</div>
                            @isset($seccion['note'])
                                <div class="pv-sec-note">{{ $seccion['note'] }}</div>
                            @endisset

                            <div class="pv-grid">
                                @foreach($seccion['options'] as $option)
                                    @php $id = $seccion['key'].'_'.$option['value']; @endphp

                                    <div class="pv-opt">
                                        <input type="radio" name="{{ $seccion['key'] }}" id="{{ $id }}"
                                               value="{{ $option['value'] }}" @checked($actual === $option['value'])>
                                        <label for="{{ $id }}">
                                            <div class="pv-head">
                                                <span class="pv-name">{{ $option['name'] }}</span>
                                                <span class="pv-tag">{{ $option['tag'] }}</span>
                                            </div>
                                            @include('managers.views.settings.portal.shot', ['shot' => $option['shot']])
                                            <p class="pv-desc">{{ $option['desc'] }}</p>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
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
<script type="text/javascript">
    $(document).ready(function () {

        $('#formPortal').on('submit', function (e) {
            e.preventDefault();

            var $submitButton = $('#formPortal button[type="submit"]');
            $submitButton.prop('disabled', true);

            var data = { _token: $('meta[name="csrf-token"]').attr('content') };

            $('#formPortal input[type="radio"]:checked').each(function () {
                data[this.name] = this.value;
            });

            $.ajax({
                url: "{{ route('manager.settings.portal.update') }}",
                type: "POST",
                data: data,
                success: function (response) {
                    if (response.success === true) {
                        toastr.success(response.message, "Operación exitosa", {
                            closeButton: true, progressBar: true, positionClass: "toast-bottom-right"
                        });
                    }
                },
                error: function (xhr) {
                    var msg = 'No se pudo guardar la configuración.';
                    if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                    toastr.error(msg, "Error", {
                        closeButton: true, progressBar: true, positionClass: "toast-bottom-right"
                    });
                },
                complete: function () {
                    $submitButton.prop('disabled', false);
                }
            });
        });

    });
</script>
@endpush
