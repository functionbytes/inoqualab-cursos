$(function () {
    'use strict';

    var $mainWrapper = $('#main-wrapper');

    $(document).on('click', '.js-sidebar-overlay', function () {
        $mainWrapper.removeClass('show-sidebar');
    });

    // app.minisidebar.init.js (compartido con managers/ y auth/) fuerza SIEMPRE
    // data-header-position/data-sidebar-position a "fixed" al cargar. En layout
    // "horizontal" ya no hace falta: la navegación vive fusionada en el header
    // (no hay aside aparte compitiendo por posición) y sin "fixed" el header
    // -- banda de contexto incluida -- fluye pegado, de ancho completo, sin
    // depender del padding-top que el tema reserva para SU cabecera (más alta).
    // En layout "vertical" no se toca: el aside sí depende de quedar fijo.
    if ($mainWrapper.data('layout') === 'horizontal') {
        $mainWrapper.attr('data-header-position', 'relative');
        $mainWrapper.attr('data-sidebar-position', 'absolute');

        // El max-width real de .container-fluid depende de reglas internas del
        // tema que varían según breakpoint y no siguen un valor fijo predecible
        // (a veces 1200px, a veces más) -- adivinar un max-width propio para la
        // banda de contexto / el carnet de configuración dejaba su contenido
        // desalineado del resto de la página en ciertos anchos de pantalla. En
        // vez de adivinar, se mide el inset real de .container-fluid respecto a
        // la ventana y se expone como variables CSS: la banda usa exactamente
        // ese mismo margen, sea cual sea, así siempre coincide.
        var syncBandInset = function () {
            var $cf = $('.body-wrapper > .container-fluid').first();
            if ($cf.length) {
                var rect = $cf[0].getBoundingClientRect();
                var pl = parseFloat($cf.css('padding-left')) || 0;
                var pr = parseFloat($cf.css('padding-right')) || 0;
                // document.documentElement.clientWidth, no window.innerWidth: el
                // segundo incluye el ancho que ocupa la barra de scroll vertical
                // (cuando la hay), el primero no -- con innerWidth el margen
                // derecho medido quedaba ~scrollbar-width px corto y el contenido
                // interno de la banda se veía desalineado del resto de la página.
                document.documentElement.style.setProperty('--cx-band-left', (rect.left + pl) + 'px');
                document.documentElement.style.setProperty('--cx-band-right', (document.documentElement.clientWidth - rect.right + pr) + 'px');
            }

            // .cx-band se "sangra" (full bleed) hasta los bordes de su propio
            // padre (<header class="app-header">, que trae padding lateral del
            // tema) -- antes con width:100vw + margin:calc(50% - 50vw), técnica
            // que también incluye el ancho de la barra de scroll en 100vw y deja
            // la banda corrida unos px hacia la izquierda (con un hueco a la
            // derecha) en cualquier página con scroll vertical, es decir, casi
            // siempre. Medir el padding real de .app-header y cancelarlo con un
            // margen negativo en píxeles concretos no depende de vw en absoluto.
            var $header = $('.app-header').first();
            if ($header.length) {
                document.documentElement.style.setProperty('--cx-band-bleed-left', (parseFloat($header.css('padding-left')) || 0) + 'px');
                document.documentElement.style.setProperty('--cx-band-bleed-right', (parseFloat($header.css('padding-right')) || 0) + 'px');
            }
        };

        syncBandInset();
        // Reajusta tras la carga completa: las fuentes web pueden reflowear el
        // layout después de document.ready y dejar la primera medición corta.
        $(window).on('load', syncBandInset);
        $(window).on('resize', syncBandInset);
        // load/resize no bastan: en mobile el JS del tema (sidebarmenu.js/
        // app.min.js) reajusta el padding de .container-fluid en un tick propio
        // después de "load" (colapsa el sidebar, etc.), y esa medición tardía se
        // perdía -- la banda quedaba con un inset ya obsoleto (15px medido vs.
        // 20px real). El ResizeObserver detecta ese cambio de tamaño sea cual
        // sea su causa y re-sincroniza, sin depender de adivinar un timeout.
        if (window.ResizeObserver) {
            var bandObserver = new ResizeObserver(syncBandInset);
            var $cfObserved = $('.body-wrapper > .container-fluid').first();
            if ($cfObserved.length) { bandObserver.observe($cfObserved[0]); }
            var $headerObserved = $('.app-header').first();
            if ($headerObserved.length) { bandObserver.observe($headerObserved[0]); }
        }
    }

    var expiredUrl = $mainWrapper.data('session-expired-url');
    var pingUrl = $mainWrapper.data('session-ping-url');

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Manejo global de sesión expirada ──────────────────────────────────────
    $(document).ajaxError(function (event, xhr) {
        if (xhr.status === 401 || xhr.status === 419) {
            window.location.href = expiredUrl;
        }
    });

    (function () {
        // Política: 2 horas de inactividad (alineado con SESSION_LIFETIME=120)
        var WARN_AFTER = 110 * 60 * 1000;  // avisar a los 110 min sin interacción
        var LOGOUT_AFTER = 120 * 60 * 1000;  // cerrar a los 120 min sin interacción
        var PING_THROTTLE = 5 * 60 * 1000;  // máximo 1 keepalive cada 5 min
        var CHECK_EVERY = 30 * 1000;  // chequeo de estado cada 30 s

        var lastActivity = Date.now();
        var lastPing = Date.now();
        var warnShown = false;

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
            '      <div class="modal-header cx-session-modal-header">',
            '        <h5 class="modal-title text-white"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cx-session-modal-icon"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Sesión por expirar</h5>',
            '      </div>',
            '      <div class="modal-body text-center py-4">',
            '        <p class="mb-1">Tu sesión cerrará por inactividad en</p>',
            '        <h2 id="sessionCountdown" class="fw-bold cx-session-modal-countdown">10:00</h2>',
            '      </div>',
            '      <div class="modal-footer justify-content-center">',
            '        <button id="sessionContinueBtn" class="btn px-5 cx-session-modal-btn">Continuar conectado</button>',
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

    $(document).on('click', '.confirm-delete', function (e) {
        e.preventDefault();
        var url = $(this).data('href');
        $('#delete-modal').modal('show');
        $('#delete-link').attr('href', url);
    });

    $(document).ajaxError(function (event, xhr) {
        var options = { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' };

        if (xhr.status === 419) {
            toastr.error('Sesión expirada. Por favor recargue la página.', 'Sesión expirada', options);
        } else if (xhr.status === 422) {
            try {
                var response = JSON.parse(xhr.responseText);
                toastr.warning(response.message || 'Error de validación.', 'Advertencia', options);
            } catch (e) {}
        } else if (xhr.status >= 500) {
            toastr.error('Error al procesar la solicitud. Intente de nuevo.', 'Error', options);
        }
    });

    var $flash = $('#cx-flash-messages');
    if ($flash.length) {
        var flashOptions = { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' };
        if ($flash.data('error')) toastr.error($flash.data('error'), 'Aviso', flashOptions);
        if ($flash.data('success')) toastr.success($flash.data('success'), 'Listo', flashOptions);
        if ($flash.data('warning')) toastr.warning($flash.data('warning'), 'Aviso', flashOptions);
    }
});
