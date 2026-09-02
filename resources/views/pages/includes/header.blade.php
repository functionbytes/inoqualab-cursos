
<!-- main header -->
<header class="main-header header-two">
    <!-- Header-Top -->
    <div class="header-top bg-dark-blue text-white">
        <div class="container">
            <div class="top-inner">
                <div class="top-left">
                    <div class=" d-flex align-items-center">
                        <div class="col-md-12 col-sm-12">
                            <p><i class="fas fa-clock"></i>  Lunes a viernes: {{ setting("page_hour_weekend") }} - Sabados: {{ setting("page_hour_weekends") }} </p>
                        </div>

                    </div>
                </div>
                <div class="top-right d-flex align-items-center">
                    @if (setting('page_email') || setting('page_phone'))
                        <ul class="top-menu">
                            @if (setting('page_email'))
                                <li><a href="mailto:{{ setting('page_email') }}"><i class="fas fa-envelope"></i> {{ setting('page_email') }}</a></li>
                            @endif
                            @if (setting('page_phone'))
                                <li><a href="tel:+{{ setting('page_phone') }}"><i class="fas fa-phone"></i> +{{ setting('page_phone') }}</a></li>
                            @endif
                        </ul>
                    @endif
                    <div class="social-style-two">
                        @if (setting('social_media_facebook')!=null)
                            <a href="{{ setting('social_media_facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        @endif
                        @if (setting('social_media_instagram')!=null)
                            <a href="{{ setting('social_media_instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                        @endif
                        @if (setting('social_media_twitter')!=null)
                            <a href="{{ setting('social_media_twitter') }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        @endif
                        @if (setting('social_media_linkedin')!=null)
                            <a href="{{ setting('social_media_linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                        @endif
                        @if (setting('social_media_youtube')!=null)
                            <a href="{{ setting('social_media_youtube') }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header-Upper -->
    <div class="header-upper">
        <div class="container clearfix">

            <div class="header-inner d-flex align-items-center justify-content-between">
                <div class="logo-outer">
                    <div class="logo">
                        <a href="{{ route('index') }}">
                              <img src="{{ getlogo() }}" width="180" alt="{{ setting('page_title') }}" />
                         </a>
                    </div>
                </div>

                <div class="nav-outer clearfix">
                    
                    <!-- Main Menu -->
                    <nav class="main-menu navbar-expand-lg">
                        <div class="navbar-header">
                            {{-- Antes usaba $setting->getMedia('logo') directo -- esa
                                 instancia de $setting (compartida a la vista) no
                                 traía el media cargado y siempre caía al fallback
                                 de texto, aunque getlogo() (usado en el logo de
                                 escritorio, .logo-outer) sí resolvía la imagen
                                 bien. Mismo helper aquí, mismo resultado. --}}
                            <div class="mobile-logo br-10 p-15">
                                <a href="{{ route('index') }}">
                                    <img src="{{ getlogo() }}" width="160" alt="{{ setting('page_title') }}" />
                                </a>
                            </div>

                            {{-- "Ingresar" vive dentro del menú desplegable (ver
                                 .navigation más abajo), no aquí aparte: con el
                                 toggle, el carrito y el botón "Ingresar" los tres
                                 visibles a la vez, la barra quedaba apretada y el
                                 acceso a la cuenta competía con el propio menú. --}}
                            <div class="mobile-header-actions">
                                {{-- Mismo icono personalizado que el carrito de escritorio
                                     (.cart-btn: caja redondeada + badge circular), en vez del
                                     ícono suelto sin estilo que tenía antes. --}}
                                <a href="{{ route('cart.index') }}" class="cart-btn position-relative" aria-label="Carrito de compras">
                                    <i class="fas fa-shopping-cart"></i>
                                    @php $cartCount = cartUnits(); @endphp
                                    @if($cartCount > 0)
                                        <span class="cart-count-badge">{{ $cartCount }}</span>
                                    @endif
                                </a>

                                {{-- Mismo avatar + dropdown que ya existe en escritorio
                                     (.iq-user-menu): antes en móvil la cuenta solo se
                                     alcanzaba enterrada dentro del offcanvas como una fila
                                     más del menú, sin el avatar con iniciales ni el acceso
                                     rápido a "Cerrar sesión". id distinto ("drop2") para no
                                     colisionar con el "drop1" de escritorio. --}}
                                @if(Auth::check())
                                    @php
                                        $userInitials = strtoupper(
                                            Str::substr(Auth::user()->firstname, 0, 1)
                                            . Str::substr(Auth::user()->lastname, 0, 1)
                                        );
                                        $userFullName = Str::words(Auth::user()->firstname, 1, '') . ' ' . Str::words(Auth::user()->lastname, 1, '');
                                    @endphp
                                    <div class="dropdown iq-user-menu">
                                        <a href="javascript:void(0)" id="drop2" class="iq-user-menu-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
                                            <i class="fa-duotone fa-light fa-user" aria-hidden="true"></i>
                                        </a>
                                        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                                            <div class="iq-user-menu-head">
                                                <span class="iq-user-menu-avatar">{{ $userInitials }}</span>
                                                <div>
                                                    <h5>{{ $userFullName }}</h5>
                                                    <span>{{ Auth::user()->email }}</span>
                                                </div>
                                            </div>
                                            <div class="iq-user-menu-links">
                                                @if (Auth::user()->role != 'customer')
                                                    <a href="{{ route('home') }}" class="iq-user-menu-item">Mi dashboard</a>
                                                @elseif (Auth::user()->role == 'customer')
                                                    <a href="{{ route('home') }}" class="iq-user-menu-item">Mi dashboard</a>
                                                    <a href="{{ route('customers.courses') }}" class="iq-user-menu-item">Mis cursos</a>
                                                @endif
                                            </div>
                                            <div class="iq-user-menu-foot">
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="iq-user-menu-logout">Cerrar sesión</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('login') }}" class="theme-btn login-icon-btn" aria-label="Ingresar"><i class="fas fa-user"></i></a>
                                @endif
                            </div>

                            {{-- Toggle: ya no abre .navbar-collapse in-flow (empujaba
                                 el contenido de la página hacia abajo) -- ahora abre
                                 el panel .vl-offcanvas (mismo patrón que inoqualab.test),
                                 deslizado desde la derecha sobre un overlay. --}}
                            <button type="button" class="navbar-toggle vl-offcanvas-toggle" aria-label="Abrir menú" aria-expanded="false">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                        <div class="navbar-collapse collapse clearfix">
                            <ul class="navigation clearfix" id="mainNavigation">
                                <li><a href="{{ route('index') }}">Inicio</a></li>
                                <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
                                <li class="dropdown"><a href="{{ route('courses') }}">Cursos <i class="fas fa-chevron-down nav-caret" aria-hidden="true"></i></a>
                                    <ul>
                                        @foreach ($allcourses as $course)
                                            <li><a href="{{ route('courses.view', array($course->slack)) }}" class="nav-course-link"> {{ str($course->title)->lower()->ucfirst() }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>
                                @if($hasActiveBundles)
                                <li><a href="{{ route('bundles') }}">Paquetes</a></li>
                                @endif
                                <li><a href="{{ route('contacts') }}">Contacto</a></li>
                                {{-- Solo en móvil: en escritorio "Ingresar"/el avatar ya
                                     viven en .menu-btns, a la derecha del menú. --}}
                                <li class="d-lg-none nav-auth-item">
                                    @if(Auth::check())
                                        <a href="{{ route('customers.dashboard') }}">Mi dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}">Ingresar</a>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <!-- Main Menu End-->
                </div>
                <div class="menu-btns d-lg-flex d-none align-items-center">
                    <a href="{{ route('cart.index') }}" class="cart-btn mr-3 position-relative" aria-label="Carrito de compras">
                        <i class="fas fa-shopping-cart"></i>
                        @php $cartCount = cartUnits(); @endphp
                        @if($cartCount > 0)
                            <span class="cart-count-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @if(Auth::check())
                            <div class="menu-btn-sidebar d-flex align-items-center">
                                <div class=" navbar-collapse justify-content-end" id="navbarNav">
                                    <div class="d-flex align-items-center justify-content-between px-0 px-xl-8">
                                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                                
                                            @php
                                                $userInitials = strtoupper(
                                                    Str::substr(Auth::user()->firstname, 0, 1)
                                                    . Str::substr(Auth::user()->lastname, 0, 1)
                                                );
                                                $userFullName = Str::words(Auth::user()->firstname, 1, '') . ' ' . Str::words(Auth::user()->lastname, 1, '');
                                            @endphp
                                            <li class="nav-item dropdown iq-user-menu">
                                                <a class="iq-user-menu-trigger" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fa-duotone fa-light fa-user" aria-hidden="true"></i>
                                                </a>
                                                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                                                    <div class="iq-user-menu-head">
                                                        <span class="iq-user-menu-avatar">{{ $userInitials }}</span>
                                                        <div>
                                                            <h5>{{ $userFullName }}</h5>
                                                            <span>{{ Auth::user()->email }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="iq-user-menu-links">
                                                        @if (Auth::user()->role != 'customer')
                                                            <a href="{{ route('home') }}" class="iq-user-menu-item">Mi dashboard</a>
                                                        @elseif (Auth::user()->role == 'customer')
                                                            <a href="{{ route('home') }}" class="iq-user-menu-item">Mi dashboard</a>
                                                            <a href="{{ route('customers.courses') }}" class="iq-user-menu-item">Mis cursos</a>
                                                        @endif
                                                    </div>
                                                    <div class="iq-user-menu-foot">
                                                        <form action="{{ route('logout') }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="iq-user-menu-logout">Cerrar sesión</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                    @else
                    <a href="{{ route('login') }}" class="theme-btn login-icon-btn" aria-label="Ingresar"><i class="fas fa-user"></i></a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!--End Header Upper-->
</header>

{{-- ===== Menú móvil tipo offcanvas (mismo patrón que inoqualab.test) =====
     Panel deslizado desde la derecha sobre un overlay oscuro, en vez del
     acordeón .navbar-collapse que empujaba el contenido de la página hacia
     abajo. El <nav> de navegación se llena por JS clonando #mainNavigation
     (el <ul> real, con los cursos ya renderizados) -- así no se duplica el
     @foreach de cursos en el Blade. --}}
<div class="vl-offcanvas">
    <div class="vl-offcanvas-wrapper">
        <div class="vl-offcanvas-header">
            <a href="{{ route('index') }}" class="vl-offcanvas-logo">
                <img src="{{ getlogo() }}" alt="{{ setting('page_title') }}" />
            </a>
            <button type="button" class="vl-offcanvas-close-toggle" aria-label="Cerrar menú">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="vl-offcanvas-menu"><nav></nav></div>

        @if (setting('page_email') || setting('page_phone'))
            <div class="vl-offcanvas-info">
                <h3 class="vl-offcanvas-sm-title">Contáctanos</h3>
                @if (setting('page_email'))
                    <a href="mailto:{{ setting('page_email') }}"><i class="fas fa-envelope" aria-hidden="true"></i> {{ setting('page_email') }}</a>
                @endif
                @if (setting('page_phone'))
                    <a href="tel:+{{ setting('page_phone') }}"><i class="fas fa-phone" aria-hidden="true"></i> +{{ setting('page_phone') }}</a>
                @endif
            </div>
        @endif

        @if (setting('social_media_facebook') || setting('social_media_instagram') || setting('social_media_twitter') || setting('social_media_linkedin') || setting('social_media_youtube'))
            <div class="vl-offcanvas-social">
                <h3 class="vl-offcanvas-sm-title">Síguenos</h3>
                @if (setting('social_media_facebook'))
                    <a href="{{ setting('social_media_facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                @endif
                @if (setting('social_media_instagram'))
                    <a href="{{ setting('social_media_instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                @endif
                @if (setting('social_media_twitter'))
                    <a href="{{ setting('social_media_twitter') }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                @endif
                @if (setting('social_media_linkedin'))
                    <a href="{{ setting('social_media_linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                @endif
                @if (setting('social_media_youtube'))
                    <a href="{{ setting('social_media_youtube') }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
                @endif
            </div>
        @endif
    </div>
</div>
<div class="vl-offcanvas-overlay"></div>

{{-- @push, no <script> inline: el header se incluye antes de que
     layouts/pages.blade.php cargue jQuery (al final del body), así que un
     <script> aquí mismo corría antes de que "$" existiera. --}}
@push('scripts')
<script>
(function () {
    // El <ul> de navegación se clona una sola vez (con los cursos ya
    // renderizados por el servidor) dentro del offcanvas, para no duplicar
    // el @@foreach de cursos en el Blade ni desincronizar ambos menús.
    var $source = $('#mainNavigation').clone().removeAttr('id');
    $('.vl-offcanvas-menu nav').append($source);

    // Submenú ("Cursos"): en vez del hover de escritorio, un botón que
    // expande/colapsa la lista de cursos in-place.
    $('.vl-offcanvas-menu nav > ul > li').each(function () {
        var $li = $(this);
        if ($li.find('> ul').length) {
            $li.append('<button type="button" class="vl-menu-close" aria-label="Mostrar submenú"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>');
        }
    });
    $('.vl-offcanvas-menu').on('click', '.vl-menu-close', function () {
        var $li = $(this).parent();
        $li.toggleClass('active');
        $li.children('ul').slideToggle(200);
    });

    function openOffcanvas() {
        $('.vl-offcanvas').addClass('vl-offcanvas-open');
        $('.vl-offcanvas-overlay').addClass('vl-offcanvas-overlay-open');
        $('.vl-offcanvas-toggle').attr('aria-expanded', 'true');
    }
    function closeOffcanvas() {
        $('.vl-offcanvas').removeClass('vl-offcanvas-open');
        $('.vl-offcanvas-overlay').removeClass('vl-offcanvas-overlay-open');
        $('.vl-offcanvas-toggle').attr('aria-expanded', 'false');
    }
    $('.vl-offcanvas-toggle').on('click', openOffcanvas);
    $('.vl-offcanvas-close-toggle, .vl-offcanvas-overlay').on('click', closeOffcanvas);
})();
</script>
@endpush
