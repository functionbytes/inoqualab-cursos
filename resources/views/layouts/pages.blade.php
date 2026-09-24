<!DOCTYPE html>

<html lang="es">

<head>

   
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="rating" content="RTA-5042-1996-1400-1577-RTA" />
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    @seoTags
    @yield('head')
    {{-- Flaticon y Nice Select retirados: 0 usos de clases "flaticon-*" o del
         plugin niceSelect() en todo resources/views/ -- ninguna vista los
         inicializa ni los pinta. Cada uno era una petición bloqueante de más
         en TODAS las páginas públicas para no aportar nada. --}}
    <!--====== Font Awesome ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/fontawesome.min.css') }}">
    <!--====== Bootstrap ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/bootstrap-4.5.3.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <!--====== Magnific Popup ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/magnific-popup.min.css') }}">
    {{-- select2 se movió a payments/register.blade.php (@push('css')): es la
         única vista que lo inicializa (#citie, #identification_type) -- antes
         se cargaba render-blocking en TODAS las páginas públicas para un
         widget que solo existe en el checkout. --}}
    {{-- jQuery UI retirado: en el template solo lo usaba el slider del filtro
         de precio (.price-slider-range en script.js), un elemento que ninguna
         vista de este proyecto pinta y cuyo bloque va protegido por un if.
         Eran 283 KB en cada visita anónima. --}}
    <!--====== Animate ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ url('/pages/css/slick.min.css') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
  

    <link rel="stylesheet" href="{{ url('/pages/css/style.css') }}?v={{ @filemtime(public_path('pages/css/style.css')) ?: '1' }}">
    @stack('css')


</head>

    <div class="">

    <div class="page-wrapper" data-web-vitals-beacon-url="{{ route('seo.web-vitals.beacon') }}">
        @include ('pages.includes.header')

        <main>
            @yield('content')
        </main>

        @include ('pages.includes.footer')

        @include ('pages.includes.socials')

        {{-- Volver arriba: dentro de .page-wrapper para competir en el mismo
             stacking context que el footer (z-index:9997) y quedar tapado por
             su fondo opaco al llegar al final de la página. --}}
        <button id="backToTop" aria-label="Volver arriba">
            <i class="fas fa-arrow-up"></i>
        </button>

    </div>

        <!--====== Bootstrap ======-->
    <script src="{{ url('pages/js/jquery-3.6.0.min.js') }}" type="text/javascript"></script>
        <!--====== Bootstrap ======-->
    <script src="{{ url('pages/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <!--====== Appear Js ======-->
    <script src="{{ url('pages/js/appear.min.js') }}" type="text/javascript"></script>
    <!--====== Slick ======-->
    <script src="{{ url('pages/js/slick.min.js') }}" type="text/javascript"></script>
    <!--====== Isotope ======-->
    <script src="{{ url('pages/js/isotope.pkgd.min.js') }}" type="text/javascript"></script>
        <script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
    <!--====== Circle Progress bar ======-->
    <script src="{{ url('pages/js/circle-progress.min.js') }}" type="text/javascript"></script>
    <!--====== Images Loader ======-->
    <script src="{{ url('pages/js/imagesloaded.pkgd.min.js') }}" type="text/javascript"></script>

    <!--====== Magnific Popup ======-->
    <script src="{{ url('pages/js/jquery.magnific-popup.min.js') }}" type="text/javascript"></script>
    <!--  WOW Animation -->
    <script src="{{ url('pages/js/wow.min.js') }}" type="text/javascript"></script>
    <!-- Custom script -->
    <script src="{{ url('pages/js/script.js') }}" type="text/javascript"></script>

    <script src="{{ url('pages/js/jquery.validate.min.js') }}" type="text/javascript"></script>

        @if(setting('google_analytics_enable') === 'true' && setting('google_analytics_measurement_id'))
        {{-- Google tag (gtag.js) GA4 --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_measurement_id') }}" data-ga-measurement-id="{{ setting('google_analytics_measurement_id') }}"></script>
        @endif

        @if(setting('meta_pixel_enable') === 'true' && setting('meta_pixel_id'))
        {{-- Meta Pixel --}}
        <div class="d-none" id="meta-pixel-config" data-meta-pixel-id="{{ setting('meta_pixel_id') }}"></div>
        <noscript><img height="1" width="1" class="d-none"
            src="https://www.facebook.com/tr?id={{ setting('meta_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
        @endif

        @if(setting('microsoft_clarity_id'))
        {{-- Microsoft Clarity --}}
        <div class="d-none" id="ms-clarity-config" data-ms-clarity-id="{{ setting('microsoft_clarity_id') }}"></div>
        @endif

        @if(setting('tiktok_pixel_id'))
        {{-- TikTok Pixel --}}
        <div class="d-none" id="tiktok-pixel-config" data-tiktok-pixel-id="{{ setting('tiktok_pixel_id') }}"></div>
        @endif

        @if(setting('linkedin_insight_tag_id'))
        {{-- LinkedIn Insight Tag --}}
        <div class="d-none" id="linkedin-insight-config" data-linkedin-insight-tag-id="{{ setting('linkedin_insight_tag_id') }}"></div>
        <noscript><img height="1" width="1" class="d-none" alt=""
            src="https://px.ads.linkedin.com/collect/?pid={{ setting('linkedin_insight_tag_id') }}&fmt=gif"/></noscript>
        @endif

        <script src="{{ asset('pages/js/layout.js') }}" type="text/javascript"></script>

    @stack('scripts')

    {{-- Modal: Curso / Paquete agregado al carrito --}}
    <div class="added-overlay" id="addedOverlay">
        <div class="added-modal" role="dialog" aria-label="Producto añadido al carrito">
            <div class="added-head">
                <div class="added-head-title">
                    Producto añadido al carrito
                </div>
                <button type="button" class="added-close" id="addedClose" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <div class="added-body">
                <div class="added-prod">
                    <div class="added-thumb" id="addedThumb"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <div class="added-name" id="addedName"></div>
                        <div class="added-ref" id="addedType"></div>
                    </div>
                </div>
                <div class="added-divider"></div>
                <div class="added-rows">
                    <div class="added-row"><span>Precio unitario</span><b id="addedUnit"></b></div>
                    <div class="added-row"><span>Cantidad</span><b id="addedQty"></b></div>
                </div>
                <div class="added-divider"></div>
                <div class="added-subtotal">
                    <span>Subtotal</span>
                    <b id="addedSubtotal"></b>
                </div>
                <div class="added-actions">
                    <a class="added-primary" id="addedCheckout" href="{{ route('checkout.cart') }}">Realizar pedido</a>
                    <button type="button" class="added-secondary" id="addedKeep">Seguir comprando</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Carrito deslizable (drawer) --}}
    <div class="cart-backdrop" id="cartBackdrop"></div>
    <aside class="cart-drawer" id="cartDrawer" aria-label="Carrito de compras"
           data-drawer-url="{{ route('cart.drawer') }}"
           data-remove-url="{{ route('cart.remove') }}"
           data-update-qty-url="{{ route('cart.update-qty') }}">
        <div class="cart-drawer-head">
            <div class="cdh-title">
                Tu carrito
                <span class="cdh-count" id="cdhCount">0</span>
            </div>
            <button type="button" class="cdh-close js-cart-close" aria-label="Cerrar carrito"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-drawer-inner" id="cartDrawerInner"></div>
    </aside>

    {{-- Llamar, WhatsApp y volver arriba: ver pages.includes.socials y el
         botón #backToTop dentro de .page-wrapper (botones flotantes estilo inoqualab).
         Botones flotantes: se "estacionan" en vez de quedar tapados por el footer
         (ver public/pages/js/layout-cart.js). --}}

    {{--
        Beacon de Core Web Vitals reales. El endpoint (seo.web-vitals.beacon,
        SeoWebVitalsController::store) ya existía y persiste en seo_web_vitals,
        pero nada en el frontend lo llamaba -- el panel manager/seo/web-vitals
        mostraba siempre "sin datos". Usa navigator.sendBeacon en vez de
        $.ajax (la convención jQuery del proyecto) a propósito: es la única
        API pensada para sobrevivir a un pagehide/unload sin cancelarse a
        mitad de camino, que es justo cuando se conocen LCP/CLS finales
        (ver public/pages/js/layout-cart.js).
    --}}

@if(request()->routeIs('index') && setting('newsletter_enabled') != '0' && setting('newsletter_popup_enabled') != '0')
<div class="d-none" id="newsletter-popup-config"
     data-delay="{{ setting('newsletter_popup_delay') ?: 2 }}"
     data-popup-url="{{ route('newsletters.ajax-popup') }}"
     data-store-url="{{ route('newsletters.store') }}"></div>
@endif

<script src="{{ asset('pages/js/layout-cart.js') }}?v={{ @filemtime(public_path('pages/js/layout-cart.js')) ?: '1' }}" type="text/javascript"></script>

</body>

</html>
