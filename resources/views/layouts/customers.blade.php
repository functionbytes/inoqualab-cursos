<!DOCTYPE html>

<html>

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>INOQUALAB - E-Learning</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
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

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ url('managers/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/quill/dist/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/dropzone/dist/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('customers/css/style.css') }}">

    @stack('css')

</head>

<body class="">

<div class="page-wrapper" id="main-wrapper" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed"
    data-header-position="fixed" data-layout="horizontal">

    @include ('customers.includes.nav')

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


<script src="{{ url('managers/libs/bootstrap-material-datetimepicker/node_modules/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/select2/dist/js/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/dropzone/dist/dropzone.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/quill/dist/quill.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/forms/select2.init.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app.minisidebar.init.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/app-style-switcher.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/sidebarmenu.js') }}" type="text/javascript"></script>
<script src="{{ url('managers/js/custom.js') }}" type="text/javascript"></script>


<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Manejo global de sesión expirada ──────────────────────────────────────
    $(document).ajaxError(function(event, xhr) {
        if (xhr.status === 401 || xhr.status === 419) {
            window.location.href = '{{ route("session.expired") }}';
        }
    });

    (function () {
        // Política: 2 horas de inactividad (alineado con SESSION_LIFETIME=120)
        var WARN_AFTER    = 110 * 60 * 1000;  // avisar a los 110 min sin interacción
        var LOGOUT_AFTER  = 120 * 60 * 1000;  // cerrar a los 120 min sin interacción
        var PING_THROTTLE =   5 * 60 * 1000;  // máximo 1 keepalive cada 5 min
        var CHECK_EVERY   =        30 * 1000;  // chequeo de estado cada 30 s
        var pingUrl       = '{{ route("customers.ping") }}';
        var expiredUrl    = '{{ route("session.expired") }}';

        var lastActivity = Date.now();
        var lastPing     = Date.now();
        var warnShown    = false;

        // Keepalive: solo renueva la sesión del servidor cuando hay interacción real (throttled)
        function keepAlive() {
            if (Date.now() - lastPing < PING_THROTTLE) return;
            lastPing = Date.now();
            $.post(pingUrl).fail(function (xhr) {
                if (xhr.status === 401 || xhr.status === 419) {
                    window.location.href = expiredUrl;
                }
            });
        }

        // Modal de aviso (Bootstrap)
        var $modal = $([
            '<div class="modal fade" id="sessionWarnModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">',
            '  <div class="modal-dialog modal-dialog-centered">',
            '    <div class="modal-content">',
            '      <div class="modal-header" style="background:#081A28;">',
            '        <h5 class="modal-title text-white"><i class="fa-duotone fa-clock me-2"></i>Sesión por expirar</h5>',
            '      </div>',
            '      <div class="modal-body text-center py-4">',
            '        <p class="mb-1">Tu sesión cerrará por inactividad en</p>',
            '        <h2 id="sessionCountdown" class="fw-bold" style="color:#081A28;">10:00</h2>',
            '      </div>',
            '      <div class="modal-footer justify-content-center">',
            '        <button id="sessionContinueBtn" class="btn px-5" style="background:#081A28;color:#fff;">Continuar conectado</button>',
            '      </div>',
            '    </div>',
            '  </div>',
            '</div>'
        ].join(''));
        $('body').append($modal);
        var bsModal = new bootstrap.Modal(document.getElementById('sessionWarnModal'));

        function fmt(ms) {
            var s = Math.max(0, Math.round(ms / 1000));
            var m = Math.floor(s / 60), sec = s % 60;
            return m + ':' + (sec < 10 ? '0' : '') + sec;
        }

        // Único reloj: basado en timestamp, robusto ante pestañas en segundo plano
        setInterval(function () {
            var idle = Date.now() - lastActivity;

            if (idle >= LOGOUT_AFTER) {
                window.location.href = expiredUrl;
                return;
            }
            if (idle >= WARN_AFTER) {
                if (!warnShown) { warnShown = true; bsModal.show(); }
                $('#sessionCountdown').text(fmt(LOGOUT_AFTER - idle));
            }
        }, CHECK_EVERY);

        // Al volver visible la pestaña, verificar de inmediato (no esperar al próximo tick)
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && Date.now() - lastActivity >= LOGOUT_AFTER) {
                window.location.href = expiredUrl;
            }
        });

        // Continuar conectado → renovar sesión y reiniciar contador
        $('#sessionContinueBtn').on('click', function () {
            lastActivity = Date.now();
            warnShown = false;
            bsModal.hide();
            lastPing = 0; keepAlive();
        });

        // Cualquier interacción real reinicia el contador de inactividad
        function onActivity() {
            lastActivity = Date.now();
            if (warnShown) { warnShown = false; bsModal.hide(); }
            keepAlive();
        }
        $(document).on('mousedown keydown touchstart scroll', onActivity);
    })();
</script>


<script>
  (function(d,t) {
    var BASE_URL="https://chat.inoqualab.com";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: 'Sn8sdgZ4toBBXcamEKoG5rco',
        baseUrl: BASE_URL
      })
    }
  })(document,"script");
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
                $("#delete-modal").modal("show");
                $("#delete-link").attr("href", url);
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

</body>

</html>
