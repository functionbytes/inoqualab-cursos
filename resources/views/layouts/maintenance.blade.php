<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $setting->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    
    
    
    
    
    

    

    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">

    @yield('head')
    {{-- Flaticon, Nice Select y jQuery UI retirados: 0 usos en todo
         resources/views/ -- mismo criterio ya aplicado en layouts/pages.blade.php.
         Los dos <link> de Font Awesome que había acá (font-awesome-5.9.0.min.css
         y managers/libs/fontawesome/fontawesome.min.css) daban 404 los dos:
         ninguno de esos archivos existe en public/. Los íconos del footer
         (pages.includes.socials, fa-phone/fa-whatsapp) no se veían en la
         página de mantenimiento real. Se reemplazan por el único que sí
         existe y ya usa el resto del sitio. --}}
    <!--====== Font Awesome ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/fontawesome.min.css') }}">
    <!--====== Bootstrap ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/bootstrap-4.5.3.min.css') }}">
    <!--====== Magnific Popup ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/magnific-popup.min.css') }}">
    <!--====== Animate ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ url('/pages/css/slick.min.css') }}">
    <link rel="stylesheet" href="{{ url('/pages/css/style.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @stack('css')


</head>

    <div class="">

    <div class="page-wrapper">

        @yield('content')

        @include ('pages.includes.socials')

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

        <script src="{{ url('managers/libs/select2/dist/js/select2.min.js') }}" type="text/javascript"></script>
        <script src="{{ url('managers/libs/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    @if(setting('google_analytics_enable') === 'true' && setting('google_analytics_measurement_id'))
    {{-- Google tag (gtag.js) GA4 -- mismo patrón que layouts/pages.blade.php. El
    snippet anterior era Universal Analytics con un tracking ID ajeno (residuo del
    template comercial) enviando pageviews a una propiedad de GA que no es de este
    proyecto; UA además dejó de recolectar datos desde julio 2023. --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_measurement_id') }}" data-ga-measurement-id="{{ setting('google_analytics_measurement_id') }}"></script>
    @endif

    <script src="{{ asset('maintenance/js/layout.js') }}" type="text/javascript"></script>

    @stack('scripts')

</body>

</html>
