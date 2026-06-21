<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'INOQUALAB')</title>
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    @seoTags

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="{{ url('/pages/css/fontawesome.min.css') }}">
    {{-- Toastr --}}
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    {{-- Sistema de estilos público (storefront) --}}
    <link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}">

    @stack('css')
</head>
<body>

    {{-- ===== Topbar: horarios + redes ===== --}}
    <div class="topbar">
        <div class="container">
            <div class="hours">
                <i class="far fa-clock"></i>
                Lunes a viernes: {{ setting('page_hour_weekend') }} · Sábados: {{ setting('page_hour_weekends') }}
            </div>
            <div class="socials">
                @if (setting('social_media_facebook'))
                    <a href="{{ setting('social_media_facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                @endif
                @if (setting('social_media_instagram'))
                    <a href="{{ setting('social_media_instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                @endif
                @if (setting('social_media_twitter'))
                    <a href="{{ setting('social_media_twitter') }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                @endif
                @if (setting('social_media_linkedin'))
                    <a href="{{ setting('social_media_linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                @endif
                @if (setting('social_media_youtube'))
                    <a href="{{ setting('social_media_youtube') }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Nav público ===== --}}
    <nav class="nav">
        <div class="container">
            <a class="brand" href="{{ route('index') }}">INOQUA<b>LAB</b><span class="dot"></span></a>
            <ul class="nav-links">
                <li><a href="{{ route('index') }}" @class(['active' => trim($__env->yieldContent('active')) === 'inicio'])>INICIO</a></li>
                <li><a href="{{ route('about') }}" @class(['active' => trim($__env->yieldContent('active')) === 'about'])>SOBRE NOSOTROS</a></li>
                <li><a href="{{ route('courses') }}" @class(['active' => trim($__env->yieldContent('active')) === 'courses'])>CURSOS</a></li>
                <li><a href="{{ route('bundles') }}" @class(['active' => trim($__env->yieldContent('active')) === 'bundles'])>PAQUETES</a></li>
                <li><a href="{{ route('contacts') }}" @class(['active' => trim($__env->yieldContent('active')) === 'contacts'])>CONTACTO</a></li>
            </ul>
            <div class="nav-right">
                <a class="cart-btn js-cart-open" href="{{ route('cart.index') }}" aria-label="Carrito">
                    <i class="fas fa-shopping-cart"></i>
                    @php $cartCount = cartUnits(); @endphp
                    @if ($cartCount > 0)
                        <span class="cart-count-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                @auth
                    <a class="btn-login" href="{{ route('customers.dashboard') }}">MI PANEL</a>
                @else
                    <a class="btn-login" href="{{ route('login') }}">INGRESAR</a>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    {{-- ===== Footer ===== --}}
    <footer class="footer">
        <div class="container">
            <div>
                <h4>Sobre nosotros</h4>
                @if (setting('page_description'))
                    {!! setting('page_description') !!}
                @else
                    <p>INOQUALAB es el soporte que usted necesita para certificar la inocuidad en sus productos.</p>
                @endif
            </div>
            <div>
                <h4>Accesos</h4>
                <ul>
                    <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
                    <li><a href="{{ route('faqs') }}">Preguntas frecuentes</a></li>
                    <li><a href="{{ route('terms') }}">Términos y condiciones</a></li>
                    <li><a href="{{ route('contacts') }}">Contáctenos</a></li>
                </ul>
            </div>
            <div>
                <h4>Contacta con nosotros</h4>
                @if (setting('page_phone'))
                    <a class="contact-row" href="tel:+{{ setting('page_phone') }}"><i class="fas fa-phone"></i> +{{ setting('page_phone') }}</a>
                @endif
                @if (setting('page_email'))
                    <a class="contact-row" href="mailto:{{ setting('page_email') }}"><i class="fas fa-envelope"></i> {{ setting('page_email') }}</a>
                @endif
                @if (setting('page_address'))
                    <div class="contact-row"><i class="fas fa-location-dot"></i> {{ setting('page_address') }}</div>
                @endif
            </div>
        </div>
    </footer>
    <div class="footer-bottom">
        <div class="container">
            {{ setting('copyright') ?: '' }} <b>INOQUALAB</b> · Todos los derechos reservados
        </div>
    </div>

    {{-- ===== Scripts base ===== --}}
    <script src="{{ url('pages/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ url('managers/libs/toastr/toastr.min.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>

    @if (setting('page_whatsapp'))
        <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}?text=Hola%2C+quiero+más+información+sobre+sus+cursos."
           target="_blank" rel="noopener" class="whatsapp-float" aria-label="Contactar por WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    @endif

    @stack('scripts')
</body>
</html>
