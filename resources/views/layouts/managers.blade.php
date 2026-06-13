<!DOCTYPE html>

<html>

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>INOQUALAB - E-Learning</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <link rel="apple-touch-icon" href="pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="Meet pages - The simplest and fastest way to build web UI for your dashboard or app." name="description" />
    <meta content="Ace" name="author" />

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
    <link rel="stylesheet" href="{{ url('managers/css/style.css') }}">
    <link rel="stylesheet" href="{{ url('managers/css/theme.css') }}">



    @stack('css')
    @yield('head')

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

    <!-- Main wrapper -->

    <div class="body-wrapper">


        @include($__panel.'.includes.header')

        <div class="container-fluid">
            @yield('content')
        </div>

        @includeFirst([$__panel.'.includes.delete', 'managers.includes.delete'])

    </div>
</div>

<script src="{{ url('managers/libs/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/simplebar/dist/simplebar.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

<!-- core files -->

s
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


<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<script>
    "use strict"
    $(function () {

        deleteConfirmation();

        // delete confirmation
        function deleteConfirmation() {
            $(".confirm-delete").click(function (e) {
                e.preventDefault();
                var url = $(this).data("href");
                $("#delete-form").attr("action", url);
                $("#delete-modal").modal("show");
            });
        }
    });

</script>

<script>
$(document).ajaxError(function(event, xhr) {
    if (xhr.status === 419) {
        toastr.error('Sesión expirada. Por favor recargue la página.', 'Sesión expirada', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    } else if (xhr.status === 422) {
        try { var res = JSON.parse(xhr.responseText); toastr.warning(res.message || 'Error de validación.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' }); } catch(e) {}
    } else if (xhr.status >= 500) {
        toastr.error('Error al procesar la solicitud. Intente de nuevo.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    }
});
</script>
@stack('scripts')

<script>
// Fix: dropdown clipped by overflow:hidden en .table-responsive
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            }
        });
    });
});
</script>

<script>
$(document).ready(function () {
    // Marcar todas las notificaciones como leídas desde el header
    $(document).on('click', '#markAllReadHeader', function (e) {
        e.preventDefault();
        $.get('{{ route('manager.notifications.markasread') }}', function () {
            $('#notifBadge').remove();
            $('#markAllReadHeader').closest('.d-flex').find('#markAllReadHeader').remove();
            toastr.success('Notificaciones marcadas como leídas', '', { timeOut: 2000 });
        });
    });
});
</script>

@includeIf($__panel.'.includes.scripts')

@yield('modal')

</body>

</html>
