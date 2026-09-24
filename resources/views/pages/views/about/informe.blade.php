@extends('layouts.pages')

{{-- "Sobre nosotros" — Diseño A "Informe de análisis": el hero toma la forma de lo
     que entrega el laboratorio (un informe con sello) sobre la foto del equipo.
     Elegible en Configuración › Sitio web (setting 'pages_about_variant'). --}}

@section('title', 'Sobre nosotros')

@push('css')
<link rel="stylesheet" href="{{ asset('pages/css/views/about/shared.css') }}?v={{ @filemtime(public_path('pages/css/views/about/shared.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('pages/css/views/about/informe.css') }}?v={{ @filemtime(public_path('pages/css/views/about/informe.css')) ?: '1' }}">
@endpush

@section('content')
@php
    $processes = [
        ['Requisitos de cumplimiento legal', 'Te acompañamos a cumplir lo que la norma sanitaria exige a tu empresa.'],
        ['Procesos comerciales más sólidos', 'Responde a las demandas del mercado sin descuidar la calidad e inocuidad de tus productos.'],
        ['Buenas prácticas de manufactura', 'Aplicadas a cada eslabón del sector agroalimentario.'],
    ];
    $advantages = [
        ['building', 'Portal empresarial', 'Un usuario para tu empresa desde el que descargas evaluaciones, resultados y certificados de tus colaboradores.'],
        ['award', 'Certificación en línea', 'Capacítate, aprueba las evaluaciones y obtén tu certificado sin salir de la plataforma.'],
        ['doc', 'Material didáctico', 'Material descargable en cada uno de los módulos.'],
        ['clock', 'Flexibilidad horaria', 'Aprende a tu ritmo y organiza tu tiempo de estudio.'],
    ];
@endphp
<div class="ab ab-a">

    <section class="ab-a-hero">
        <div class="ab-wrap ab-a-hero-grid">
            <div>
                <p class="ab-lede">Sobre INOQUALAB</p>
                <h1 class="ab-display ab-a-title">Analizamos alimentos y formamos a quienes los manipulan.</h1>
                <p class="ab-a-intro">Es un laboratorio con amplia trayectoria en el servicio de análisis microbiológico y fisicoquímico de alimentos y aguas, con alto nivel tecnológico; creado en la ciudad de Bucaramanga y líder en el oriente colombiano.</p>
                <div class="ab-actions">
                    <a href="{{ route('courses') }}" class="ab-btn ab-btn--navy">Ver cursos</a>
                    <a href="{{ route('contacts') }}" class="ab-btn ab-btn--ghost">Portal para empresas</a>
                </div>
            </div>

            <div class="ab-a-visual">
                <div class="ab-photo ab-a-hero-photo">
                    <img src="{{ asset('pages/images/about/about-two1.jpg') }}" alt="Equipo de INOQUALAB en el laboratorio">
                </div>
                <div class="ab-a-report">
                    <div class="ab-a-report-head">
                        <span class="ab-display">Informe INOQUALAB</span>
                        <span>Resultado conforme</span>
                    </div>
                    <dl class="ab-a-report-rows">
                        <div><dt>Laboratorio</dt><dd>Bucaramanga, Santander</dd></div>
                        <div><dt>Tipo de análisis</dt><dd>Microbiológico y fisicoquímico</dd></div>
                        <div><dt>Matrices</dt><dd>Alimentos y aguas</dd></div>
                        <div><dt>Clientes capacitados</dt><dd>{{ number_format($users, 0, ',', '.') }}</dd></div>
                        <div><dt>Empresas vinculadas</dt><dd>{{ number_format($enterprises, 0, ',', '.') }}</dd></div>
                        <div><dt>Satisfacción</dt><dd>99 %</dd></div>
                    </dl>
                    <div class="ab-a-stamp" aria-hidden="true">
                        @include('pages.partials.sections.about.icon', ['name' => 'flask'])
                        <span class="ab-display">Excelencia en el servicio</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ab-a-who">
        <div class="ab-wrap ab-a-who-grid">
            <div class="ab-photo ab-a-who-photo">
                <img src="{{ asset('pages/images/about/mision.jpg') }}" alt="Analista de INOQUALAB en el laboratorio" loading="lazy">
            </div>
            <div class="ab-a-who-copy">
                <h2 class="ab-display ab-a-h2">Quiénes somos</h2>
                <p class="ab-a-big">Contamos con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continua de los procesos de producción de su empresa.</p>
                <p class="ab-a-text">Nuestros equipos de última tecnología y plataformas de información para el análisis de muestras y capacitación permiten asegurar la trazabilidad, entregando resultados confiables, exactos y oportunos.</p>

                <div class="ab-a-help">
                    <h3>En qué ayudamos a tu empresa</h3>
                    @foreach ($processes as [$title, $text])
                        <div class="ab-a-help-item">
                            <span class="ab-a-check">@include('pages.partials.sections.about.icon', ['name' => 'check'])</span>
                            <div>
                                <strong>{{ $title }}</strong>
                                <p>{{ $text }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="ab-a-adv">
        <div class="ab-wrap">
            <h2 class="ab-display ab-a-h2 ab-a-adv-title">Por qué capacitar a tu equipo con nosotros</h2>
            <div class="ab-a-adv-grid">
                <article class="ab-a-adv-feature">
                    <img src="{{ asset('pages/images/about/about-two2.jpg') }}" alt="" loading="lazy">
                    <div class="ab-a-adv-feature-copy">
                        <span class="ab-a-adv-icon ab-a-adv-icon--solid">@include('pages.partials.sections.about.icon', ['name' => 'law'])</span>
                        <h3 class="ab-display">Cumplimiento normativo</h3>
                        <p>La Resolución 2674 de 2013 y las demás normas de obligatorio cumplimiento para el sector de alimentos y su cadena de abastecimiento.</p>
                    </div>
                </article>
                <div class="ab-a-adv-list">
                    @foreach ($advantages as [$icon, $title, $text])
                        <article class="ab-a-adv-card">
                            <span class="ab-a-adv-icon">@include('pages.partials.sections.about.icon', ['name' => $icon])</span>
                            <h3 class="ab-display">{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="ab-a-mv">
        <div class="ab-wrap ab-a-mv-grid">
            <article class="ab-a-mv-item">
                <h2 class="ab-display ab-a-mv-label">Misión</h2>
                <p class="ab-a-mv-main">Somos un medio de comunicación que ofrece la mejor información del marco legal para colaboradores de empresas del sector de alimentos; bajo la modalidad virtual, potenciando el aprendizaje autónomo y colaborativo, que ayuda a mejorar la gestión en los procesos de Buenas Prácticas de Manufactura.</p>
                <p class="ab-a-mv-sub">Contamos con profesionales microbiólogos e ingenieros químicos, debidamente inscritos y autorizados por la autoridad competente.</p>
            </article>
            <article class="ab-a-mv-item">
                <h2 class="ab-display ab-a-mv-label">Visión</h2>
                <p class="ab-a-mv-main">La plataforma de capacitación virtual de INOQUALAB S.A.S. proyecta ser reconocida por la industria de alimentos como la herramienta que permite contar con programas de apoyo didáctico en la formación de los manipuladores.</p>
            </article>
        </div>
    </section>

    @include('pages.partials.sections.about.cta', ['tone' => 'navy'])
</div>
@endsection
