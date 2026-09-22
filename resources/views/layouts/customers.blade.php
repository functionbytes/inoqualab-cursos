<!DOCTYPE html>

<html lang="es">

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>@hasSection('title')@yield('title') · @endif{{ setting('page_title') ?: 'INOQUALAB - E-Learning' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <link rel="apple-touch-icon" href="{{ url('pages/ico/60.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ url('pages/ico/76.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ url('pages/ico/120.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('pages/ico/152.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    
    
    
    
    
    

    

    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Este layout venía heredando la lista de librerías del panel de gestión.
         El portal del alumno no usa select2, quill, dropzone ni daterangepicker
         en ninguna de sus 35 vistas (verificado también en las partials y en los
         JS del tema), así que se quitan: eran ~570 KB por página. --}}
    <link rel="stylesheet" href="{{ url('managers/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/css/style.css') }}">
    {{-- Versionado por fecha de modificación: sin él, el navegador del alumno
         sigue sirviendo el CSS anterior tras cada despliegue. --}}
    <link rel="stylesheet" href="{{ url('customers/css/portal.css') }}?v={{ @filemtime(public_path('customers/css/portal.css')) ?: 1 }}">
    <link rel="stylesheet" href="{{ url('customers/css/layout.css') }}">

    @stack('css')

</head>

<body class="">

@php
    // La posición del menú se elige en Configuración › Portal del alumno y vale
    // para todo el portal: si dependiera de cada vista, la navegación saltaría
    // de arriba al lateral al cambiar de pantalla. El valor está validado al
    // guardarse; aquí se filtra igualmente por si la fila se edita a mano.
    $navLayout = setting('customers_nav_layout', 'horizontal') === 'vertical' ? 'vertical' : 'horizontal';
@endphp
<div class="page-wrapper" id="main-wrapper" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed"
    data-header-position="fixed" data-layout="{{ $navLayout }}"
    data-session-expired-url="{{ route('session.expired') }}" data-session-ping-url="{{ route('customers.ping') }}">

    <div class="dark-transparent js-sidebar-overlay"></div>

    {{-- El nav vertical clásico (aside) solo aplica al layout "vertical" --
         en "horizontal" la navegación ahora vive fusionada en la fila del
         header (customers.includes.header) y en el menú del avatar en móvil. --}}
    @if ($navLayout === 'vertical')
        @include ('customers.includes.nav')
    @endif

    <!-- Main wrapper -->

    <div class="body-wrapper">


        @include ('customers.includes.header')

        <div class="container-fluid">
            @yield('content')
        </div>

        @include ('customers.includes.support')

    </div>
</div>

<script src="{{ url('managers/libs/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/simplebar/dist/simplebar.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

<!-- core files -->


<script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.minisidebar.init.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app-style-switcher.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/sidebarmenu.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/custom.js') }}" type="text/javascript"></script>

@if(session('error') || session('success') || session('warning'))
<div id="cx-flash-messages" class="d-none"
     data-error="{{ session('error') }}"
     data-success="{{ session('success') }}"
     data-warning="{{ session('warning') }}"></div>
@endif

<script src="{{ asset('customers/js/layout.js') }}" type="text/javascript"></script>
<script src="{{ asset('customers/js/includes/style-vars.js') }}" type="text/javascript"></script>

@stack('scripts')

</body>

</html>
