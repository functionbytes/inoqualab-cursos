<!DOCTYPE html>

<html>

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>@hasSection('title')@yield('title') · @endif{{ setting('page_title') ?: 'INOQUALAB - E-Learning' }}</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <link rel="apple-touch-icon" href="{{ url('pages/ico/60.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ url('pages/ico/76.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ url('pages/ico/120.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('pages/ico/152.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">

    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="stylesheet" href="{{ url('managers/libs/taginput/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/quill/dist/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/dropzone/dist/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('managers/css/style.css') }}?v={{ filemtime(public_path('managers/css/style.css')) }}">
    <link rel="stylesheet" href="{{ url('managers/css/theme.css') }}?v={{ filemtime(public_path('managers/css/theme.css')) }}">
    {{-- Copia literal de modules/Theme/public/theme/css/nav.css (webadmin):
         define .app-header/.app-wrapper/.app-menubar-tabs. Se carga despues
         de style.css/theme.css a proposito para ganar en cascada sobre las
         reglas .app-header antiguas de Bootstrap Modernize (mismo nombre de
         clase, sistema de shell distinto). --}}
    <link rel="stylesheet" href="{{ url('managers/css/nav.css') }}?v={{ filemtime(public_path('managers/css/nav.css')) }}">
    {{-- Directo, no @push: @stack('css') se renderiza aqui en <head>, pero
         includes.header vive en <body> (mas abajo) — cualquier @push('css')
         suyo se registra demasiado tarde para este @stack y nunca sale. --}}
    <link rel="stylesheet" href="{{ url('managers/css/includes/header.css') }}?v={{ filemtime(public_path('managers/css/includes/header.css')) }}">

    @stack('css')
    @yield('head')

    {{-- Se carga en ultimo lugar a proposito, despues de @stack('css') (CSS
         especifico de cada vista): fija el color real de .badge por encima
         de los presets de color rotos de theme.css. Ver comentario en el
         propio archivo. --}}
    <link rel="stylesheet" href="{{ url('managers/css/badge-colors.css') }}?v={{ filemtime(public_path('managers/css/badge-colors.css')) }}">

    {{-- Pista visual de scroll horizontal en .table-responsive (mobile).
         Fix generico para todas las vistas, no depende de @stack('css') de
         ninguna en particular. Ver comentario en el propio archivo. --}}
    <link rel="stylesheet" href="{{ url('managers/css/table-scroll-hint.css') }}?v={{ filemtime(public_path('managers/css/table-scroll-hint.css')) }}">

    {{-- Estado de carga de managers/js/ajax-table.js (busqueda/filtro/paginacion
         sin recargar la pagina). --}}
    <link rel="stylesheet" href="{{ url('managers/css/includes/ajax-table.css') }}?v={{ filemtime(public_path('managers/css/includes/ajax-table.css')) }}">

    {{-- FOUC: el estado contraido del riel se guarda en localStorage y se
         aplica antes del primer pintado para que al navegar no se vea el
         menu abrirse y cerrarse (ver managers/js/includes/app-toggler.js). --}}
    <script>
    (function () {
        try {
            if (window.innerWidth > 1480 && localStorage.getItem('mc-app-sidebar') === 'mini') {
                document.documentElement.setAttribute('data-app-sidebar', 'mini');
            }
        } catch (e) {}
    })();
    </script>

</head>

<body class="">

<div class="mc-app" id="mc-app" data-notifications-mark-all-read-url="{{ route('manager.notifications.markasread') }}">

    @php
        // Layout unificado: resuelve nav/header/delete según el rol del usuario.
        // manager/superadmin usan el panel "managers"; cada otro perfil el suyo.
        $__panel = match (auth()->user()?->role) {
            'support' => 'supports',
            'distributor' => 'distributors',
            'enterprise' => 'enterprises',
            'accounting' => 'accountings',
            default => 'managers',
        };
    @endphp

    @include($__panel.'.includes.nav')

    <div class="mc-app-body app-wrapper">

        @include($__panel.'.includes.header')

        {{-- mc-content-header (managers.includes.card): va full-bleed, fuera
             del container-fluid, igual que en webadmin (@yield('page_header')
             antes de <main>). El contenido de la pagina si va contenido. --}}
        @hasSection('page_header')
            @yield('page_header')
        @endif

        <main class="mc-content">
            <div class="container">
                @yield('content')
            </div>
        </main>

        @includeFirst([$__panel.'.includes.delete', 'managers.includes.delete'])

    </div>
</div>

<script src="{{ url('managers/libs/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/simplebar/dist/simplebar.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

<!-- core files -->

<script src="{{ url('managers/libs/taginput/bootstrap-tagsinput.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/bootstrap-material-datetimepicker/node_modules/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/select2/dist/js/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/dropzone/dist/dropzone.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/quill/dist/quill.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/forms/select2.init.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.minisidebar.init.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app-style-switcher.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/sidebarmenu.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/flatpickr.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/custom.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/bulk-actions.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/filter-toolbar.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/ajax-table.js') }}" type="text/javascript"></script>

<script src="{{ asset('managers/js/layout.js') }}" type="text/javascript"></script>
<script src="{{ asset('managers/js/table-scroll-hint.js') }}" type="text/javascript"></script>
<script src="{{ asset('managers/js/dropdown-boundary-fix.js') }}" type="text/javascript"></script>

@stack('scripts')

@includeIf($__panel.'.includes.scripts')

@yield('modal')

</body>

</html>
