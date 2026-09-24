@extends('layouts.managers')

@section('title', 'Sitio web')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/portal/setting.css') }}">
{{-- Fuentes del sitio público (layouts/pages): la vista previa de la tarjeta debe verse con la misma tipografía. --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Figtree:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="{{ asset('pages/css/partials/components/course-card.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/website/setting.css') }}">
@endpush

@php
    $cardOptions = [
        ['value' => 'a', 'name' => 'Hermana del paquete', 'tag' => 'Opción A',
         'desc' => 'Misma estructura que la tarjeta de paquete: precio antes/ahora entre separadores y botón a ancho completo.'],
        ['value' => 'b', 'name' => 'Precio sobre la imagen', 'tag' => 'Opción B',
         'desc' => 'El precio va encima de la foto con un degradado oscuro y el enlace "Ver curso" al pie. Es la tarjeta más baja de las cuatro.'],
        ['value' => 'c', 'name' => 'Ficha con datos', 'tag' => 'Opción C',
         'desc' => 'Foto encajada con la categoría encima, título en mayúscula, clases/temas/calificación en una mini tabla, precio antes/ahora y botón a ancho completo.'],
        ['value' => 'd', 'name' => 'Base oscura corporativa', 'tag' => 'Opción D',
         'desc' => 'Tarjeta blanca con una franja azul marino al pie que lleva el precio y la llamada a la acción.'],
    ];
    $currentCard = setting('pages_course_card_variant', 'c');

    $detailOptions = [
        ['value' => '1', 'name' => 'Compra a la derecha', 'tag' => 'Modalidad 1', 'image' => 'course-detail-1.jpg',
         'desc' => 'Diseño actual: hero azul marino con los datos del curso y la tarjeta de compra fija a la derecha, con duración, clases, temas, examen y certificado.'],
        ['value' => '2', 'name' => 'Editorial con imagen y ruta', 'tag' => 'Modalidad 2', 'image' => 'course-detail-2.jpg',
         'desc' => 'Hero a pantalla completa con la foto del curso y una tarjeta de compra blanca a la derecha con lo que incluye. Debajo, secciones numeradas: descripción, objetivos en tarjetas, ruta por módulos, contenido por tipo de clase y certificador. Barra de compra fija al bajar.'],
    ];
    $currentDetail = (string) setting('pages_course_detail_variant', '1');

    $aboutOptions = [
        ['value' => '1', 'name' => 'Versión anterior', 'tag' => 'Original', 'image' => 'about-1.jpg',
         'desc' => 'El diseño que tenía la página: collage de fotos con "Quiénes somos", procesos sobre fondo de laboratorio y ventajas.'],
        ['value' => 'a', 'name' => 'Informe de análisis', 'tag' => 'Diseño A', 'image' => 'about-a.jpg',
         'desc' => 'Sobre la foto del equipo flota un informe del laboratorio con sus cifras y un sello de "Excelencia en el servicio".'],
        ['value' => 'b', 'name' => 'Placa de Petri', 'tag' => 'Diseño B', 'image' => 'about-b.jpg',
         'desc' => 'Una placa circular con la analista y las cifras alrededor como colonias. Título: "Lo que no se ve, también se controla."'],
        ['value' => 'c', 'name' => 'Norma 2674', 'tag' => 'Diseño C', 'image' => 'about-c.jpg',
         'desc' => 'El número de la Resolución 2674 enorme detrás del título, y las cifras dentro de una frase. Misión y visión en dos bloques de color.'],
        ['value' => 'd', 'name' => 'Combinada (A + C)', 'tag' => 'Diseño D', 'image' => 'about-d.jpg',
         'desc' => 'El hero con informe y sello de A, "Quiénes somos" y las ventajas en bloque, y misión y visión en dos bloques de color como C.'],
    ];
    $currentAbout = (string) setting('pages_about_variant', '1');
@endphp

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Sitio web',
        'description' => 'Diseño de los componentes del sitio público',
    ])
@endsection

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <form id="formWebsite" role="form" data-update-url="{{ route('manager.settings.website.update') }}">
                {{ csrf_field() }}

                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Diseño del sitio público</h6>
                    <p class="text-muted small mb-0">
                        Elige qué diseño ven los visitantes en cada componente. El cambio se aplica de inmediato para todos.
                    </p>
                </div>

                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Tarjeta de curso</h6>
                    <p class="text-muted small mb-3">
                        Se usa en el catálogo (/courses), en las dos secciones de cursos del inicio y en "Cursos relacionados" del detalle de cada curso.
                    </p>
                    @if($previewCourse)
                        <p class="ws-preview-note">
                            Vista previa con un curso real del catálogo: <strong>{{ str($previewCourse->title)->lower()->ucfirst() }}</strong>.
                        </p>
                    @endif
                    <div class="pv-grid ws-card-grid">
                        @foreach($cardOptions as $option)
                            @php $id = 'pages_course_card_variant_'.$option['value']; @endphp

                            <div class="pv-opt">
                                <input type="radio" name="pages_course_card_variant" id="{{ $id }}"
                                       value="{{ $option['value'] }}" @checked($currentCard === $option['value'])>
                                <label for="{{ $id }}">
                                    <div class="pv-head">
                                        <span class="pv-name">{{ $option['name'] }}</span>
                                        <span class="pv-tag">{{ $option['tag'] }}</span>
                                        <span class="ws-in-use">{!! \App\Html\IconHelper::render('check-circle', 14) !!} En uso</span>
                                    </div>
                                    @if($previewCourse)
                                        <div class="ws-preview-stage">
                                            <div class="crs-preview" inert>
                                                @include('pages.partials.components.course-card', ['course' => $previewCourse, 'variant' => $option['value']])
                                            </div>
                                        </div>
                                    @endif
                                    <p class="pv-desc">{{ $option['desc'] }}</p>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-0">

                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Detalle de curso</h6>
                    <p class="text-muted small mb-3">
                        La página de cada curso (por ejemplo /courses/{{ $previewCourse->slack ?? 'CODIGO' }}). Ambas modalidades usan los mismos datos, botones de compra y cursos relacionados.
                    </p>
                    <div class="pv-grid ws-detail-grid">
                        @foreach($detailOptions as $option)
                            @php $id = 'pages_course_detail_variant_'.$option['value']; @endphp

                            <div class="pv-opt">
                                <input type="radio" name="pages_course_detail_variant" id="{{ $id }}"
                                       value="{{ $option['value'] }}" @checked($currentDetail === $option['value'])>
                                <label for="{{ $id }}">
                                    <div class="pv-head">
                                        <span class="pv-name">{{ $option['name'] }}</span>
                                        <span class="pv-tag">{{ $option['tag'] }}</span>
                                        <span class="ws-in-use">{!! \App\Html\IconHelper::render('check-circle', 14) !!} En uso</span>
                                    </div>
                                    <div class="ws-shot">
                                        <img src="{{ asset('managers/images/settings/website/'.$option['image']) }}"
                                             alt="Vista previa: {{ $option['name'] }}" loading="lazy">
                                    </div>
                                    <p class="pv-desc">{{ $option['desc'] }}</p>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-0">

                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Sobre nosotros</h6>
                    <p class="text-muted small mb-3">
                        La página /about. Todas las opciones muestran el mismo contenido de la empresa, las cifras de clientes y empresas y los datos de contacto de los ajustes.
                    </p>
                    <div class="pv-grid ws-detail-grid">
                        @foreach($aboutOptions as $option)
                            @php $id = 'pages_about_variant_'.$option['value']; @endphp

                            <div class="pv-opt">
                                <input type="radio" name="pages_about_variant" id="{{ $id }}"
                                       value="{{ $option['value'] }}" @checked($currentAbout === $option['value'])>
                                <label for="{{ $id }}">
                                    <div class="pv-head">
                                        <span class="pv-name">{{ $option['name'] }}</span>
                                        <span class="pv-tag">{{ $option['tag'] }}</span>
                                        <span class="ws-in-use">{!! \App\Html\IconHelper::render('check-circle', 14) !!} En uso</span>
                                    </div>
                                    <div class="ws-shot">
                                        <img src="{{ asset('managers/images/settings/website/'.$option['image']) }}"
                                             alt="Vista previa: {{ $option['name'] }}" loading="lazy">
                                    </div>
                                    <p class="pv-desc">{{ $option['desc'] }}</p>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/website/setting.js') }}"></script>
@endpush
