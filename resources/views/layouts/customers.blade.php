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
    data-header-position="fixed" data-layout="{{ $navLayout }}">

    <div class="dark-transparent" onclick="document.getElementById('main-wrapper').classList.remove('show-sidebar')"></div>

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
@if ($navLayout === 'horizontal')
<script>
    // app.minisidebar.init.js (compartido con managers/ y auth/) fuerza SIEMPRE
    // data-header-position/data-sidebar-position a "fixed" al cargar. En layout
    // "horizontal" ya no hace falta: la navegación vive fusionada en el header
    // (no hay aside aparte compitiendo por posición) y sin "fixed" el header
    // -- banda de contexto incluida -- fluye pegado, de ancho completo, sin
    // depender del padding-top que el tema reserva para SU cabecera (más alta).
    // En layout "vertical" no se toca: el aside sí depende de quedar fijo.
    $(function () {
        $('#main-wrapper').attr('data-header-position', 'relative');
        $('#main-wrapper').attr('data-sidebar-position', 'absolute');
    });

    // El max-width real de .container-fluid depende de reglas internas del
    // tema que varían según breakpoint y no siguen un valor fijo predecible
    // (a veces 1200px, a veces más) -- adivinar un max-width propio para la
    // banda de contexto / el carnet de configuración dejaba su contenido
    // desalineado del resto de la página en ciertos anchos de pantalla. En
    // vez de adivinar, se mide el inset real de .container-fluid respecto a
    // la ventana y se expone como variables CSS: la banda usa exactamente
    // ese mismo margen, sea cual sea, así siempre coincide.
    function syncBandInset() {
        var $cf = $('.body-wrapper > .container-fluid').first();
        if (!$cf.length) return;
        var rect = $cf[0].getBoundingClientRect();
        var pl = parseFloat($cf.css('padding-left')) || 0;
        var pr = parseFloat($cf.css('padding-right')) || 0;
        document.documentElement.style.setProperty('--cx-band-left', (rect.left + pl) + 'px');
        document.documentElement.style.setProperty('--cx-band-right', (window.innerWidth - rect.right + pr) + 'px');
    }
    syncBandInset();
    // Reajusta tras la carga completa: las fuentes web pueden reflowear el
    // layout después de document.ready y dejar la primera medición corta.
    $(window).on('load', syncBandInset);
    $(window).on('resize', syncBandInset);
</script>
@endif
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
            '        <h5 class="modal-title text-white"><svg viewBox=\"0 0 24 24\" width=\"18\" height=\"18\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:-3px;margin-right:8px\"><circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M12 7v5l3 2\"/></svg>Sesión por expirar</h5>',
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

@if(session('error') || session('success') || session('warning'))
<script>
    @if(session('error'))
        toastr.error({!! json_encode(session('error')) !!}, 'Aviso', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    @endif
    @if(session('success'))
        toastr.success({!! json_encode(session('success')) !!}, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    @endif
    @if(session('warning'))
        toastr.warning({!! json_encode(session('warning')) !!}, 'Aviso', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    @endif
</script>
@endif
@stack('scripts')

</body>

</html>
