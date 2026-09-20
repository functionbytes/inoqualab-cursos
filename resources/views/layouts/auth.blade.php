<!DOCTYPE html>

<html>

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    @seoTags
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <link rel="apple-touch-icon" href="pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="Ace" name="author" />

    
    
    
    
    
    

    

    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ url('customers/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/libs/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/libs/quill/dist/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/fontawesome/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/libs/dropzone/dist/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/libs/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('customers/css/style.css') }}">

    @stack('css')

</head>

<body class="">

<div
        class="page-wrapper"
        id="main-wrapper"
        data-layout="vertical"
        data-navbarbg="skin6"
        data-sidebartype="full"
        data-sidebar-position="fixed"
        data-header-position="fixed"
>

    <!-- Main wrapper -->



            @yield('content')

</div>

<script src="{{ url('customers/libs/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/simplebar/dist/simplebar.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

<!-- core files -->


<script src="{{ url('customers/libs/bootstrap-material-datetimepicker/node_modules/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/select2/dist/js/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/dropzone/dist/dropzone.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/libs/quill/dist/quill.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/forms/select2.init.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/app.minisidebar.init.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/app-style-switcher.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/sidebarmenu.js') }}" type="text/javascript"></script>
<script src="{{ url('customers/js/custom.js') }}" type="text/javascript"></script>

<script src="{{ asset('auth/js/layout.js') }}" type="text/javascript"></script>

@stack('scripts')

</body>

</html>
