@extends('layouts.pages')
@section('title', 'Sobre nosotros')
@section('content')


<section class="about-section-two pb-120 padding-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-collage rmb-75 wow fadeInUp delay-0-2s animated">
                    <div class="about-collage-main">
                        <img src="/pages/images/about/about-four1.jpg" alt="Equipo INOQUALAB">
                    </div>
                    <span class="about-collage-badge about-collage-badge--top">
                        <i class="fas fa-award" aria-hidden="true"></i>
                        Laboratorio acreditado
                    </span>
                    <span class="about-collage-badge about-collage-badge--bottom">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        Excelencia en el servicio
                    </span>
                    <div class="about-collage-small">
                        <img src="/pages/images/about/about-four2.jpg" alt="Análisis en el laboratorio">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content-two  wow fadeInUp delay-0-2s animated">
                    <div class="section-title mb-30">
                       <span class="sub-title">INOQUALAB</span>
                        <h2>Quienes somos</h2>
                    </div>
                    <p class="text-justify">Es un laboratorio con amplia trayectoria en el servicio de ANÁLISIS microbiológico y fisicoquímico de alimentos y aguas, con alto nivel tecnológico; creado en la ciudad de Bucaramanga y líder en el oriente colombiano.</p>
                    <p class="text-justify">Contamos con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continua de los procesos de producción de su empresa. </p>
                    <p class="text-justify">Nuestros equipos de última tecnología y plataformas de información para el análisis de muestras y capacitación, permiten asegurar la trazabilidad, entregando resultados confiables, exactos y oportunos.</p>
                    <div class="counter-wrap">
                        <div class="success-item counted">
                            <span class="count-text plus" data-speed="3000" data-stop="256">{{ $users }}</span>
                            <span>CLIENTES</span>
                        </div>
                        <div class="success-item counted">
                            <span class="count-text plus" data-speed="3000" data-stop="2.36">{{ $enterprises }}</span>
                            <span>EMPRESAS</span>
                        </div>
                        <div class="success-item counted">
                            <span class="count-text percent" data-speed="3000" data-stop="99">99</span>
                            <span>SATISFACCIÓN</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== Foto de fondo + badge + tarjetas =====
     Patron tomado del bloque "Intro Video" del template Finsta (imagen
     full-bleed con overlay oscuro, badge tipo pildora arriba y una fila de
     tarjetas blancas debajo, todo dentro de la misma seccion) -- mismo
     contenido que tenia la version anterior (badge INOQUALAB, parrafo y los
     3 puntos de la lista), solo cambia la presentacion: en vez de 2 columnas
     texto+imagen, ahora la imagen es el fondo completo de la seccion. --}}
<section class="about-feature-photo">
    <div class="container">
        <div class="afp-media wow fadeInUp delay-0-2s animated">
            <img src="/pages/images/about/about-two2.jpg" alt="Equipo INOQUALAB">
            <div class="afp-overlay">
                <span class="afp-badge">INOQUALAB</span>
                <p class="afp-desc">INOQUALAB cuenta con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continúa de los procesos de:</p>
            </div>
            <div class="afp-cards">
                <div class="afp-card">
                    <span class="afp-card-icon"><i class="fas fa-check" aria-hidden="true"></i></span>
                    <p>Requisitos de cumplimiento legal.</p>
                </div>
                <div class="afp-card">
                    <span class="afp-card-icon"><i class="fas fa-check" aria-hidden="true"></i></span>
                    <p>Mejorar sus procesos comerciales respondiendo a las demandas del mercado, salvaguardando la calidad e inocuidad de sus productos.</p>
                </div>
                <div class="afp-card">
                    <span class="afp-card-icon"><i class="fas fa-check" aria-hidden="true"></i></span>
                    <p>Buenas prácticas de manufactura dentro del sector agroalimentario.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="iq-values-section">
    <div class="container">
        <div class="section-title text-center mb-0 wow fadeInUp delay-0-2s animated">
            <span class="sub-title">SERVICIOS</span>
            <h2>Nuestros servicios</h2>
            <p class="text-justify">INOQUALAB con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continúa de los procesos de:</p>
        </div>

        <div class="iq-values">
            <div class="iq-value-item iq-value-item--featured">
                <div class="iq-value-body">
                    <span class="iq-value-icon"><img src="/pages/images/icons/compliance.svg" alt=""></span>
                    <div class="iq-value-content">
                        <h3>Cumplimiento normativo</h3>
                        <p>El cumplimiento de la Resolución 2674 de 2013 y otras normas específicas que son de obligatorio cumplimiento para las empresas de sector de alimentos y su cadena de abastecimiento.</p>
                    </div>
                </div>
            </div>

            <div class="iq-value-item">
                <div class="iq-value-body">
                    <span class="iq-value-icon"><img src="/pages/images/icons/enterprise.svg" alt=""></span>
                    <div class="iq-value-content">
                        <h3>Portal empresarial</h3>
                        <p>Creamos un usuario empresas en el cual puedes descargar evaluaciones, resultados y certificados de sus colaboradores.</p>
                    </div>
                </div>
            </div>

            <div class="iq-value-item">
                <div class="iq-value-body">
                    <span class="iq-value-icon"><img src="/pages/images/icons/certification.svg" alt=""></span>
                    <div class="iq-value-content">
                        <h3>Certificación en línea</h3>
                        <p>Capacítate con nosotros, aprueba las evaluaciones y certifícate fácilmente.</p>
                    </div>
                </div>
            </div>

            <div class="iq-value-item">
                <div class="iq-value-body">
                    <span class="iq-value-icon"><img src="/pages/images/icons/material.svg" alt=""></span>
                    <div class="iq-value-content">
                        <h3>Material didáctico</h3>
                        <p>Material didáctico descargable disponible en cada uno de los módulos.</p>
                    </div>
                </div>
            </div>

            <div class="iq-value-item">
                <div class="iq-value-body">
                    <span class="iq-value-icon"><img src="/pages/images/icons/schedule.svg" alt=""></span>
                    <div class="iq-value-content">
                        <h3>Flexibilidad horaria</h3>
                        <p>Aprende a tu propio ritmo y maneja tu tiempo de estudio.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
   
    {{-- ===== Misión y visión: tarjetas =====
         Patron tomado del bloque "Our Approach" del template Finsta (about.html):
         titulo centrado + fila de tarjetas sobre fondo gris, cada una con una
         imagen recortada arriba, titulo con linea separadora y texto debajo.
         Reemplaza las 2 secciones anteriores (columnas texto+imagen alternadas,
         una por Mision y otra por Vision) -- mismo contenido, nueva presentacion. --}}
    <section class="iq-approach-section">
        <div class="container">
            <div class="section-title text-center mb-0 wow fadeInUp delay-0-2s animated">
                <span class="sub-title">INOQUALAB</span>
                <h2>Misión y visión</h2>
            </div>

            <div class="iq-approach-grid">
                <div class="iq-approach-card wow fadeInUp delay-0-2s animated">
                    <div class="iq-approach-media">
                        <figure>
                            <img src="/pages/images/about/mision.jpg" alt="Misión INOQUALAB">
                        </figure>
                    </div>
                    <h3>Misión</h3>
                    <p>Somos un medio de comunicación que ofrece la mejor información del marco legal para colaboradores de empresas del sector de alimentos; bajo la modalidad virtual, potenciando el aprendizaje autónomo y colaborativo, que ayudan a mejorar la gestión en los procesos de Buenas Prácticas de Manufactura.</p>
                    <p>Contamos con profesionales microbiólogos e ingenieros químicos, debidamente inscritos y autorizados por la autoridad competente.</p>
                </div>

                <div class="iq-approach-card wow fadeInUp delay-0-4s animated">
                    <div class="iq-approach-media">
                        <figure>
                            <img src="/pages/images/about/vision.jpg" alt="Visión INOQUALAB">
                        </figure>
                    </div>
                    <h3>Visión</h3>
                    <p>La plataforma de capacitación virtual de INOQUALAB S.A.S proyecta ser reconocido por la industria de alimentos, como la herramienta desarrollada que permite contar con programas de apoyo didáctico en la formación de los manipuladores...</p>
                </div>
            </div>
        </div>
    </section>
@endsection
