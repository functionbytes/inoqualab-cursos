@extends('layouts.pages')
@section('title', 'Sobre nosotros')
@section('content')


<section class="about-section-two pb-120 padding-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-two-left rmb-75 wow fadeInUp delay-0-2s animated">
                    <div class="about-two-images">
                        <img src="/pages/images/about/about-two1.jpg" alt="About">
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
<section class="about-section-two pb-120 padding-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-content-two wow fadeInUp delay-0-2s animated">
                    <div class="section-title mb-30">
                        <span class="sub-title">INOQUALAB</span>
                        <p class="text-justify">INOQUALAB cuenta con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control
                        de la calidad y la mejora continúa de los procesos de:</p>
                    </div>
                    <ul class="list-style-one pt-10 pb-45">
                        <li class="text-justify">Requisitos de cumplimiento legal.</li>
                        <li class="text-justify">Mejorar sus procesos comerciales respondiendo a las demandas del mercado, salvaguardando la calidad e inocuidad de sus productos.</li>
                        <li class="text-justify">Buenas prácticas de manufactura dentro del sector agroalimentario.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-two-left rmb-75 wow fadeInUp delay-0-2s animated">
                    <div class="about-two-images">
                        <img src="/pages/images/about/about-two2.jpg" alt="About">
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-section-three rel z-1 pt-110 rpt-85 pb-100 rpb-70">
    <div class="container">
        <div class="section-title text-center mb-55 wow fadeInUp delay-0-2s animated"">
            <span class="sub-title">SERVICIOS</span>
            <p class="text-justify">INOQUALAB con un grupo de profesionales en alimentos, dispuestos a ofrecer sus conocimientos y experiencia en el control de la calidad y la mejora continúa de los procesos de:</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-4 col-sm-12">
                <div class=" card feature-three-item wow fadeInUp delay-0-4s animated text-center">
                    <div class="icon">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <p class="text-justify">El cumplimiento de la Resolución 2674 de 2013 y otras normas específicas que son de obligatorio cumplimiento para las
                    empresas de sector de alimentos y su cadena de abastecimiento.</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card feature-three-item wow fadeInUp delay-0-4s animated text-center">
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <p class="text-justify">Creamos un usuario empresas en el cual puedes descargar evaluaciones, resultados y certificados de sus colaboradores.</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card feature-three-item wow fadeInUp delay-0-4s animated text-center">
                    <div class="icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <p class="text-justify">Capacítate con nosotros, aprueba las evaluaciones y certifícate fácilmente..</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card feature-three-item wow fadeInUp delay-0-4s animated text-center">
                    <div class="icon">
                       <i class="fas fa-file-invoice"></i>
                    </div>
                    <p class="text-justify">Material didáctico descargable.</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card feature-three-item wow fadeInUp delay-0-4s animated text-center">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="text-justify">Manejo de tu tiempo.</p>
                </div>
            </div>
            
        </div>
    </div>
</section>
   
    <section class="why-choose-section pt-120 rpt-90 pb-130 rpb-100">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-6">
                    <div class="why-choose-content rmb-80 wow fadeInUp delay-0-2s animated"">
                        <div class="section-title mb-25">
                            <span class="sub-title">INOQUALAB</span>
                            <h2>MISIÓN</h2>
                        </div>
                        <p class="text-justify">Somos un medio de comunicación que ofrece la mejor información del marco legal para colaboradores de empresas del
                        sector de alimentos; bajo la modalidad virtual, potenciando el aprendizaje autónomo y colaborativo, que ayudan a mejorar
                        la gestión en los procesos de Buenas Prácticas de Manufactura.</p>
                        <p class="text-justify">Contamos con profesionales microbiólogos e ingenieros químicos, debidamente inscritos y autorizados por la autoridad
                        competente.</p>
                    </div>
                </div>
                <div class="col-lg-5">
                        <div class="why-learn-image wow fadeInUp delay-0-2s animated">
                            <img src="/pages/images/about/mision.jpg" alt="Why Learn">
                        </div>
                </div>
            </div>
        </div>
    </section>
<section class="why-choose-section pt-120 rpt-90 pb-130 rpb-100">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-5">  
                    <div class="why-learn-image wow fadeInUp delay-0-2s animated"">
                        <img src="/pages/images/about/vision.jpg" alt="Why Learn">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="why-choose-content rmb-80 wow fadeInUp delay-0-2s animated">
                        <div class="section-title mb-25">
                            <span class="sub-title">INOQUALAB</span>
                            <h2>VISIÓN</h2>
                        </div>
                        <p class="text-justify">La plataforma de capacitación virtual de INOQUALAB S.A.S proyecta ser reconocido por la industria de alimentos, como la
                        herramienta desarrollada que permite contar con programas de apoyo didáctico en la formación de los manipuladores...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
