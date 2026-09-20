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

@push('scripts')
<script src="{{ asset('customers/js/partials/views/courses/assessment-exit-guard.js') }}"></script>
@endpush
