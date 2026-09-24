@extends('layouts.pages')

{{-- "Sobre nosotros" — Diseño C "Norma 2674": tipografía como protagonista, con el
     número de la Resolución 2674 de 2013 enorme detrás del título. Las cifras van
     dentro de una frase, no como tarjetas de "número grande + etiqueta".
     Elegible en Configuración › Sitio web (setting 'pages_about_variant'). --}}

@section('title', 'Sobre nosotros')

@push('css')
<link rel="stylesheet" href="{{ asset('pages/css/views/about/shared.css') }}?v={{ @filemtime(public_path('pages/css/views/about/shared.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('pages/css/views/about/norma.css') }}?v={{ @filemtime(public_path('pages/css/views/about/norma.css')) ?: '1' }}">
@endpush

@section('content')
@php
    $processes = [
        ['Requisitos de cumplimiento legal', 'Te acompañamos a cumplir lo que la norma sanitaria exige a tu empresa.'],
        ['Procesos comerciales más sólidos', 'Responde a las demandas del mercado sin descuidar la calidad e inocuidad de tus productos.'],
        ['Buenas prácticas de manufactura', 'Aplicadas a cada eslabón del sector agroalimentario.'],
    ];
    $advantages = [
        ['law', 'Cumplimiento normativo', 'La Resolución 2674 de 2013 y las normas de obligatorio cumplimiento para el sector de alimentos.'],
        ['building', 'Portal empresarial', 'Descarga evaluaciones, resultados y certificados de tus colaboradores.'],
        ['award', 'Certificación en línea', 'Aprueba las evaluaciones y obtén tu certificado en la plataforma.'],
        ['doc', 'Material didáctico', 'Material descargable en cada uno de los módulos.'],
        ['clock', 'Flexibilidad horaria', 'Aprende a tu ritmo y organiza tu tiempo de estudio.'],
    ];
@endphp
<div class="ab ab-c">

    <section class="ab-c-hero">
        <div class="ab-c-number" aria-hidden="true">2674</div>
        <div class="ab-wrap ab-c-hero-inner">
            <div class="ab-c-hero-copy">
                <p class="ab-lede ab-c-lede">Resolución 2674 de 2013</p>
                <h1 class="ab-display ab-c-title">Cumplir la norma sanitaria empieza por tu equipo.</h1>
                <p class="ab-c-intro">Somos un laboratorio de análisis de alimentos y aguas en Bucaramanga. Formamos a los manipuladores de tu empresa en buenas prácticas de manufactura, con cursos virtuales y certificado.</p>
                <div class="ab-actions">
                    <a href="{{ route('courses') }}" class="ab-btn ab-btn--blue">Ver cursos</a>
                    <a href="{{ route('contacts') }}" class="ab-btn ab-btn--ghost-light">Portal para empresas</a>
                </div>
            </div>
            <div class="ab-c-strip">
                <div class="ab-photo"><img src="{{ asset('pages/images/about/about-two1.jpg') }}" alt="Equipo de INOQUALAB"></div>
                <div class="ab-photo"><img src="{{ asset('pages/images/about/mision.jpg') }}" alt="Analista en el laboratorio"></div>
                <div class="ab-photo"><img src="{{ asset('pages/images/about/about-four2.jpg') }}" alt="Estudiante en su curso virtual"></div>
            </div>
        </div>
    </section>

    <section class="ab-c-sentence">
        <div class="ab-wrap">
            <p class="ab-display">Hemos capacitado a <span>{{ number_format($users, 0, ',', '.') }} personas</span> de <span>{{ number_format($enterprises, 0, ',', '.') }} empresas</span>, y el <span>99 %</span> queda satisfecho con su formación.</p>
        </div>
    </section>

    <section class="ab-c-who">
        <div class="ab-wrap ab-c-who-grid">
            <div>
                <h2 class="ab-display ab-c-h2">Quiénes somos</h2>
                <p class="ab-c-big">Es un laboratorio con amplia trayectoria en el servicio de análisis microbiológico y fisicoquímico de alimentos y aguas, con alto nivel tecnológico; creado en la ciudad de Bucaramanga y líder en el oriente colombiano.</p>
                <p class="ab-c-text">Contamos con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continua de los procesos de producción de su empresa.</p>
                <p class="ab-c-text">Nuestros equipos de última tecnología y plataformas de información para el análisis de muestras y capacitación permiten asegurar la trazabilidad, entregando resultados confiables, exactos y oportunos.</p>
            </div>
            <div class="ab-c-help">
                <h3 class="ab-display">En qué ayudamos a tu empresa</h3>
                @foreach ($processes as [$title, $text])
                    <div class="ab-c-help-item">
                        <strong>{{ $title }}</strong>
                        <p>{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ab-c-adv">
        <div class="ab-wrap">
            <div class="ab-c-adv-box">
                <h2 class="ab-display ab-c-h2">Por qué capacitar a tu equipo con nosotros</h2>
                <div class="ab-c-adv-grid">
                    @foreach ($advantages as [$icon, $title, $text])
                        <article class="ab-c-adv-item">
                            <span class="ab-c-adv-icon">@include('pages.partials.sections.about.icon', ['name' => $icon])</span>
                            <h3 class="ab-display">{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="ab-c-mv">
        <div class="ab-c-mv-half ab-c-mv-half--navy">
            <div class="ab-c-mv-copy">
                <h2 class="ab-display ab-c-mv-label">Misión</h2>
                <p class="ab-c-mv-main">Somos un medio de comunicación que ofrece la mejor información del marco legal para colaboradores de empresas del sector de alimentos; bajo la modalidad virtual, potenciando el aprendizaje autónomo y colaborativo, que ayuda a mejorar la gestión en los procesos de Buenas Prácticas de Manufactura.</p>
                <p class="ab-c-mv-sub">Contamos con profesionales microbiólogos e ingenieros químicos, debidamente inscritos y autorizados por la autoridad competente.</p>
            </div>
        </div>
        <div class="ab-c-mv-half ab-c-mv-half--blue">
            <div class="ab-c-mv-copy">
                <h2 class="ab-display ab-c-mv-label">Visión</h2>
                <p class="ab-c-mv-main">La plataforma de capacitación virtual de INOQUALAB S.A.S. proyecta ser reconocida por la industria de alimentos como la herramienta que permite contar con programas de apoyo didáctico en la formación de los manipuladores.</p>
            </div>
        </div>
    </section>

    @include('pages.partials.sections.about.cta', ['tone' => 'navy'])
</div>
@endsection
