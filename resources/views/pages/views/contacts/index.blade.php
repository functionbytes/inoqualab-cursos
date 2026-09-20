@extends('layouts.pages')

@section('title', 'Contacto')

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
    <link rel="stylesheet" href="{{ asset('pages/css/views/contacts/index.css') }}">
@endpush

@section('content')

<div class="band">
    <div class="container">
        <div class="crumb">
            <a href="{{ route('index') }}">INICIO</a>
            <span class="sep">/</span>
            <span class="cur">CONTACTO</span>
        </div>
        <h1>Contacto</h1>
        <p class="lede">Escríbenos y un asesor te responderá lo antes posible.</p>
    </div>
</div>

<section class="padding-top padding-bottom">
    <div class="container">
        <div class="section-title text-center mb-0 wow fadeInUp delay-0-2s animated">
            <span class="sub-title">Atención directa</span>
            <h2>Hablemos</h2>
            <p class="text-justify iq-contact-intro-text">Valoramos la retroalimentación de nuestros clientes y estamos aquí para responder a tus preguntas, escuchar tus comentarios y proporcionar cualquier ayuda que necesites.</p>
        </div>

        <div class="row g-4 iq-contact-cards">
            <div class="col-md-6 col-lg-3">
                <div class="iq-contact-card">
                    <div class="iq-contact-card-icon"><i class="fab fa-whatsapp"></i></div>
                    <div class="iq-contact-card-content">
                        <h3>WhatsApp</h3>
                        <p>La vía más rápida para escribirnos.</p>
                        <ul><li><a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener">{{ setting('page_whatsapp') }}</a></li></ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-contact-card">
                    <div class="iq-contact-card-icon"><i class="fas fa-phone"></i></div>
                    <div class="iq-contact-card-content">
                        <h3>Teléfono</h3>
                        <p>Llámanos en horario de atención.</p>
                        <ul><li><a href="tel:+{{ setting('page_phone') }}">+{{ setting('page_phone') }}</a></li></ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-contact-card">
                    <div class="iq-contact-card-icon"><i class="fas fa-envelope"></i></div>
                    <div class="iq-contact-card-content">
                        <h3>Correo electrónico</h3>
                        <p>Escríbenos tus dudas.</p>
                        <ul><li><a href="mailto:{{ setting('page_email') }}">{{ setting('page_email') }}</a></li></ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-contact-card">
                    <div class="iq-contact-card-icon"><i class="fas fa-clock"></i></div>
                    <div class="iq-contact-card-content">
                        <h3>Horario</h3>
                        <p>Nuestra atención al público.</p>
                        <ul>
                            <li>Lunes - Viernes: {{ setting('page_hour_weekend') }}</li>
                            <li>Sabados: {{ setting('page_hour_weekends') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="iq-form-band">
    <div class="container text-center">
        <span class="sub-title">Escríbenos</span>
        <h2>Envíanos tu solicitud</h2>
        <p class="iq-form-band-text">Cuéntanos qué necesitas y un asesor te responderá lo antes posible.</p>

        <div class="iq-form-card text-start">
            <h3>Contacto</h3>
            <p>Si tienes dudas o quieres saber más, nos pondremos en contacto contigo.</p>

            <form id="formContacts" class="account__form" enctype="multipart/form-data" role="form" onSubmit="return false" data-store-url="{{ route('contacts.store') }}">

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Nombres" required="">
                        </div>
                        <label id="firstname-error" class="error d-none" for="firstname"></label>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Apellidos" required="">
                        </div>
                        <label id="lastname-error" class="error d-none" for="lastname"></label>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" required="">
                        </div>
                        <label id="email-error" class="error d-none" for="email"></label>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="cellphone" name="cellphone" class="form-control" placeholder="Celular" required="">
                        </div>
                        <label id="cellphone-error" class="error d-none" for="cellphone"></label>
                    </div>
                    <div class="col-md-12">
                        <div class="input-group">
                            <textarea name="message" id="message" placeholder="Ingresa su mensaje" class="form-control" rows="4" required=""></textarea>
                        </div>
                        <label id="message-error" class="error d-none" for="message"></label>
                    </div>
                </div>

                <div class="account__form-passcheck">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms">
                        <label for="terms" class="form-check-label">
                            Acepta <a href="{{ route('terms') }}">términos y condiciones</a>.
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="errors d-none">
                    </div>
                </div>

                <button type="button" id="submitContacts" class="btn btn-primary contact-disabled w-100">Enviar</button>

            </form>
        </div>
    </div>
</section>

@include ('pages.partials.sections.pages.home-faq')

@endsection






@push('scripts')
    <script src="{{ asset('pages/js/views/contacts/index.js') }}"></script>
@endpush

