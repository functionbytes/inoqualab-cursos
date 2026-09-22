@extends('layouts.managers')

@section('title', 'Portal del alumno')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/portal/setting.css') }}">
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

@section('page_header')
    @include('managers.includes.card', ['title' => 'Portal del alumno'])
@endsection

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <form id="formPortal" role="form" data-update-url="{{ route('manager.settings.portal.update') }}">
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
<script src="{{ asset('managers/js/views/settings/portal/setting.js') }}"></script>
@endpush
