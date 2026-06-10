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
    <meta content="Meet pages - The simplest and fastest way to build web UI for your dashboard or app."
        name="description" />
    <meta content="Ace" name="author" />

    {!! SEOMeta::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}
    {!! JsonLdMulti::generate() !!}
    {!! SEO::generate() !!}
    {!! SEO::generate(true) !!}

    {!! app('seotools')->generate() !!}

    {!! Html::favicon( getFavicon() ) !!}

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ url('managers/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/quill/dist/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/dropzone/dist/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/css/style.css') }}">

    @stack('css')

</head>

<body class="">

    <div class="page-wrapper" id="main-wrapper" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-layout="horizontal">

        @include ('supports.includes.nav')

        <!-- Main wrapper -->

        <div class="body-wrapper">


            @include ('supports.includes.header')

            <div class="container-fluid">
                @yield('content')
            </div>

            @include('supports.includes.delete')

        </div>
    </div>

    <script src="{{ url('managers/libs/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/simplebar/dist/simplebar.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

    <!-- core files -->


    <script src="{{ url('managers/libs/bootstrap-material-datetimepicker/node_modules/moment/moment.js') }}"
        type="text/javascript"></script>
    <script src="{{ url('managers/libs/select2/dist/js/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript">
    </script>
    <script src="{{ url('managers/libs/dropzone/dist/dropzone.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/quill/dist/quill.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/js/forms/select2.init.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/js/app.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/js/app.minisidebar.init.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/js/app-style-switcher.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/js/sidebarmenu.js') }}" type="text/javascript"></script>
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

        var deleteUrl = null;

        // Abrir modal de confirmacion y guardar la URL destino
        $(document).on("click", ".confirm-delete", function (e) {
            e.preventDefault();
            deleteUrl = $(this).data("href");
            $("#delete-modal").modal("show");
        });

        // Confirmar: enviar peticion DELETE via form (preserva el redirect del controlador)
        $(document).on("click", "#delete-link", function (e) {
            e.preventDefault();
            if (!deleteUrl) return;

            var token = $('meta[name="csrf-token"]').attr('content');

            var $form = $('<form>', { method: 'POST', action: deleteUrl, 'class': 'd-none' });
            $form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
            $form.append($('<input>', { type: 'hidden', name: '_token', value: token }));

            $('body').append($form);
            $form.trigger('submit');
        });
    });

    </script>

    <script>
    // Manejo global de errores AJAX: reactiva botones submit bloqueados y
    // muestra mensajes de validacion en todas las vistas del panel de soporte.
    $(document).ajaxError(function(event, xhr) {

        // Reactivar cualquier boton submit que la vista haya deshabilitado,
        // para que el usuario pueda reintentar tras un error.
        $('button[type="submit"]:disabled').prop('disabled', false);

        if (xhr.status === 419) {
            toastr.error('Sesión expirada. Por favor recargue la página.', 'Sesión expirada', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
        } else if (xhr.status === 422) {
            var messages = [];
            var res = xhr.responseJSON;

            if (res && res.errors) {
                $.each(res.errors, function(field, errs) {
                    messages.push(Array.isArray(errs) ? errs[0] : errs);
                });
            } else if (res && res.message) {
                messages.push(res.message);
            }

            // Pintar el detalle en el contenedor .errors si la vista lo tiene.
            var $box = $('.errors');
            if ($box.length && messages.length) {
                $box.removeClass('d-none').html(messages.join('<br>'));
            }

            toastr.warning(messages[0] || 'Error de validación.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
        } else if (xhr.status === 403) {
            toastr.error('No tienes autorización para realizar esta acción.', 'Acceso denegado', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
        } else if (xhr.status >= 500) {
            toastr.error('Error al procesar la solicitud. Intente de nuevo.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
        }
    });
    </script>
    @stack('scripts')

    @yield('modal')
    
</body>

</html>