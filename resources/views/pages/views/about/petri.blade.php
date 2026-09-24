@extends('layouts.pages')

{{-- "Sobre nosotros" — Diseño B "Placa de Petri": lo invisible (la microbiología)
     como motivo. Una placa circular con la analista y las cifras como "colonias".
     Elegible en Configuración › Sitio web (setting 'pages_about_variant'). --}}

@section('title', 'Sobre nosotros')

@push('css')
<link rel="stylesheet" href="{{ asset('pages/css/views/about/shared.css') }}?v={{ @filemtime(public_path('pages/css/views/about/shared.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('pages/css/views/about/petri.css') }}?v={{ @filemtime(public_path('pages/css/views/about/petri.css')) ?: '1' }}">
@endpush

@section('content')
@php
    $processes = [
        ['law', 'Requisitos de cumplimiento legal', 'Te acompañamos a cumplir lo que la norma sanitaria exige a tu empresa.'],
        ['chart', 'Procesos comerciales más sólidos', 'Responde a las demandas del mercado sin descuidar la calidad e inocuidad de tus productos.'],
        ['flask', 'Buenas prácticas de manufactura', 'Aplicadas a cada eslabón del sector agroalimentario.'],
    ];
    $advantages = [
        ['law', 'Cumplimiento normativo', 'La Resolución 2674 de 2013 y las demás normas de obligatorio cumplimiento para el sector de alimentos y su cadena de abastecimiento.'],
        ['building', 'Portal empresarial', 'Un usuario para tu empresa desde el que descargas evaluaciones, resultados y certificados de tus colaboradores.'],
        ['award', 'Certificación en línea', 'Capacítate, aprueba las evaluaciones y obtén tu certificado sin salir de la plataforma.'],
        ['doc', 'Material didáctico', 'Material descargable en cada uno de los módulos.'],
        ['clock', 'Flexibilidad horaria', 'Aprende a tu ritmo y organiza tu tiempo de estudio.'],
    ];
@endphp
<div class="ab ab-b">

    <section class="ab-b-hero">
        <div class="ab-wrap ab-b-hero-grid">
            <div>
                <p class="ab-lede">Laboratorio de análisis de alimentos y aguas</p>
                <h1 class="ab-display ab-b-title">Lo que no se ve, también se controla.</h1>
                <p class="ab-b-intro">Desde Bucaramanga analizamos la calidad microbiológica y fisicoquímica de lo que llega a la mesa, y capacitamos a los equipos que manipulan alimentos para que la cuiden.</p>
                <div class="ab-actions">
                    <a href="{{ route('courses') }}" class="ab-btn ab-btn--blue">Ver cursos</a>
                    <a href="{{ route('contacts') }}" class="ab-btn ab-btn--ghost">Portal para empresas</a>
                </div>
            </div>

            {{-- La placa: el elemento memorable de este diseño --}}
            <div class="ab-b-dish">
                <div class="ab-b-dish-rim" aria-hidden="true"></div>
                <div class="ab-b-dish-plate">
                    <img src="{{ asset('pages/images/about/mision.jpg') }}" alt="Analista de INOQUALAB en el laboratorio">
                </div>
                <div class="ab-b-colony ab-b-colony--lg">
                    <strong class="ab-display">{{ number_format($users, 0, ',', '.') }}</strong>
                    <span>clientes</span>
                </div>
                <div class="ab-b-colony ab-b-colony--md">
                    <strong class="ab-display">{{ number_format($enterprises, 0, ',', '.') }}</strong>
                    <span>empresas</span>
                </div>
                <div class="ab-b-colony ab-b-colony--sm">
                    <strong class="ab-display">99 %</strong>
                    <span>satisfacción</span>
                </div>
            </div>
        </div>
    </section>

    <section class="ab-b-who">
        <div class="ab-wrap ab-b-who-grid">
            <div>
                <h2 class="ab-display ab-b-h2">Un laboratorio líder en el oriente colombiano</h2>
                <p class="ab-b-big">Contamos con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continua de los procesos de producción de su empresa.</p>
                <p class="ab-b-text">Nuestros equipos de última tecnología y plataformas de información para el análisis de muestras y capacitación permiten asegurar la trazabilidad, entregando resultados confiables, exactos y oportunos.</p>
            </div>
            <div class="ab-b-team">
                <div class="ab-b-team-photo">
                    <img src="{{ asset('pages/images/about/about-two1.jpg') }}" alt="Equipo de INOQUALAB" loading="lazy">
                </div>
                <span class="ab-b-badge">
                    <span>@include('pages.partials.sections.about.icon', ['name' => 'award'])</span>
                    Laboratorio acreditado
                </span>
            </div>
        </div>
    </section>

    <section class="ab-b-help">
        <div class="ab-wrap">
            <h2 class="ab-display ab-b-h2 ab-b-center">En qué ayudamos a tu empresa</h2>
            <div class="ab-b-help-grid">
                @foreach ($processes as [$icon, $title, $text])
                    <article class="ab-b-help-item">
                        <span class="ab-b-help-ring">@include('pages.partials.sections.about.icon', ['name' => $icon])</span>
                        <h3 class="ab-display">{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ab-b-adv">
        <div class="ab-wrap ab-b-adv-grid">
            <div>
                <h2 class="ab-display ab-b-h2 ab-b-light">Por qué capacitar a tu equipo con nosotros</h2>
                <div class="ab-b-adv-photo">
                    <img src="{{ asset('pages/images/about/about-four1.jpg') }}" alt="Estudiante tomando un curso" loading="lazy">
                </div>
            </div>
            <div class="ab-b-adv-list">
                @foreach ($advantages as [$icon, $title, $text])
                    <article class="ab-b-adv-item">
                        <span class="ab-b-adv-icon">@include('pages.partials.sections.about.icon', ['name' => $icon])</span>
                        <div>
                            <h3 class="ab-display">{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ab-b-mv">
        <div class="ab-wrap ab-b-mv-grid">
            <article class="ab-b-mv-item">
                <h2 class="ab-display ab-b-mv-label"><span aria-hidden="true"></span>Misión</h2>
                <p class="ab-b-mv-main">Somos un medio de comunicación que ofrece la mejor información del marco legal para colaboradores de empresas del sector de alimentos; bajo la modalidad virtual, potenciando el aprendizaje autónomo y colaborativo, que ayuda a mejorar la gestión en los procesos de Buenas Prácticas de Manufactura.</p>
                <p class="ab-b-mv-sub">Contamos con profesionales microbiólogos e ingenieros químicos, debidamente inscritos y autorizados por la autoridad competente.</p>
            </article>
            <article class="ab-b-mv-item">
                <h2 class="ab-display ab-b-mv-label"><span aria-hidden="true"></span>Visión</h2>
                <p class="ab-b-mv-main">La plataforma de capacitación virtual de INOQUALAB S.A.S. proyecta ser reconocida por la industria de alimentos como la herramienta que permite contar con programas de apoyo didáctico en la formación de los manipuladores.</p>
            </article>
        </div>
    </section>

    @include('pages.partials.sections.about.cta', ['tone' => 'blue'])
</div>
@endsection
