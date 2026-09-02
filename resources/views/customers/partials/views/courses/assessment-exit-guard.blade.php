{{--
    Guard de salida para quiz y examen.

    Objetivo: que el alumno no abandone el cuestionario a medias sin darse cuenta.
    No es DRM (nada en el navegador es infranqueable), pero cubre las tres salidas
    accidentales reales:
      1. Recargar / cerrar la pestaña / navegar fuera del sitio  -> aviso nativo (beforeunload).
      2. Clic en el menú de lecciones, "Volver al curso" o el menú superior -> modal de confirmación.
      3. El envío legítimo del propio cuestionario NO dispara ningún aviso.

    El "envío legítimo" se detecta observando el submit de #question-form cuando ya
    no viene prevenido por la confirmación de envío que cada vista maneja aparte.
--}}
<div class="modal fade" id="assessmentExitModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ax-modal">
            <div class="modal-body">
                <div class="ax-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <h5 class="ax-title">¿Salir del cuestionario?</h5>
                <p class="ax-text">Si sales ahora perderás las respuestas que llevas. Termina y envía el cuestionario para conservar tu resultado.</p>
                <div class="ax-actions">
                    <button type="button" class="ax-btn ax-stay" data-bs-dismiss="modal" id="assessmentExitStay">Seguir en el cuestionario</button>
                    <button type="button" class="ax-btn ax-leave" id="assessmentExitLeave">Salir de todos modos</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var inProgress = true;

    // 1) Recarga, cierre de pestaña o navegación fuera del sitio.
    window.addEventListener('beforeunload', function (e) {
        if (inProgress) { e.preventDefault(); e.returnValue = ''; return ''; }
    });

    // 2) Envío real del cuestionario: cada vista previene el primer submit para
    //    mostrar su modal de "confirmar envío"; cuando el submit YA no viene
    //    prevenido es el envío de verdad y se levanta el guard.
    //    Red de seguridad, no la vía principal: $(form).submit() de jQuery, al no
    //    venir prevenido, delega en el form.submit() nativo del DOM -- y ESE método
    //    no dispara el evento 'submit' (solo form.requestSubmit() lo hace), así que
    //    este listener nunca llegaba a correr y el guard seguía "en progreso" hasta
    //    el beforeunload real, mostrando el diálogo nativo del navegador de más.
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.id === 'question-form' && !e.defaultPrevented) {
            inProgress = false;
        }
    });

    // Vía principal: cada vista llama esto en el click de su botón "Aceptar" del
    // modal de confirmación, justo antes de disparar el submit real -- ahí SÍ
    // sabemos con certeza que es un envío legítimo, sin depender de si el navegador
    // llega a emitir o no el evento 'submit' nativo.
    window.releaseAssessmentGuard = function () {
        inProgress = false;
    };

    // 3) Clic en cualquier navegación que sacaría del cuestionario.
    //    El modal se instancia de forma perezosa en el momento del clic: este
    //    script corre dentro del contenido de la vista, antes de que bootstrap.js esté
    //    cargado, así que crearlo aquí daría undefined.
    var pending = null;
    var modalEl = document.getElementById('assessmentExitModal');

    function exitModal() {
        if (!modalEl || !window.bootstrap) { return null; }
        return bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    document.addEventListener('click', function (e) {
        if (!inProgress) { return; }

        var nav = e.target.closest('a[href], button');
        if (!nav) { return; }

        // Todo lo que ocurre dentro del propio cuestionario se permite
        // (elegir opción, pregunta siguiente/anterior, enviar).
        if (nav.closest('#question-form')) { return; }

        // Los botones de cualquier modal (este guard, o el de confirmar envío)
        // manejan su propia lógica y NO son navegación. Sin esta exclusión, el
        // clic en "Salir de todos modos" se interceptaba a sí mismo: sobreescribía
        // el destino pendiente con su propio botón y no llevaba a ningún sitio.
        if (nav.closest('.modal')) { return; }

        // Controles que abren/cierran paneles pero no navegan.
        if (nav.hasAttribute('data-bs-toggle') || nav.closest('[data-bs-toggle]')) { return; }
        if (nav.closest('.lv-rail-toggle')) { return; }

        // Enlaces sin destino real (#, anclas) no sacan de la página.
        var href = nav.getAttribute('href');
        if (nav.tagName === 'A' && (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0)) { return; }

        // Es una salida de verdad: interceptar y confirmar con el modal.
        e.preventDefault();
        e.stopPropagation();
        pending = nav;

        var m = exitModal();
        if (m) {
            m.show();
        } else {
            // Sin bootstrap no se bloquea con un confirm() nativo (congelaría la
            // pestaña): se deja salir. El beforeunload sigue cubriendo recarga/cierre.
            inProgress = false;
            leave(nav);
        }
    }, true);

    function leave(el) {
        if (!el) { return; }
        if (el.tagName === 'A') { window.location.href = el.href; }
        else { el.click(); } // botón de navegación del rail (submit de su propio form)
    }

    var leaveBtn = document.getElementById('assessmentExitLeave');
    if (leaveBtn) {
        leaveBtn.addEventListener('click', function () {
            inProgress = false;
            var m = exitModal();
            if (m) { m.hide(); }
            leave(pending);
        });
    }
})();
</script>
