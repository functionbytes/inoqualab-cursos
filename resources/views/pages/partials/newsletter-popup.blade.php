<div class="modal fade" id="newsletter-popup-modal" tabindex="-1" role="dialog" aria-modal="true" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="nl-close" id="newsletter-popup-close" aria-label="Cerrar">&times;</button>

            <div class="nl-accent">Recibe novedades, promociones.</div>

            <div class="nl-body">
                <h4 class="nl-title">Mantente informado y aprovecha más.</h4>
                <p class="nl-desc">Suscríbete y recibe en tu correo nuestras últimas actualizaciones, promociones exclusivas y contenido de valor.</p>

                <form id="newsletter-popup-form" autocomplete="off">
                    <label class="nl-label" for="newsletter-popup-name">Nombre</label>
                    <input id="newsletter-popup-name" type="text" class="nl-input" placeholder="Ingresa tu nombre">

                    <label class="nl-label" for="newsletter-popup-email">
                        Dirección de correo electrónico <span class="req">*</span>
                    </label>
                    <input id="newsletter-popup-email" type="email" class="nl-input" placeholder="Ingresa tu correo electrónico" required>

                    <div id="newsletter-popup-error" class="nl-error"></div>

                    <button type="submit" class="nl-btn">Suscribir</button>
                </form>

                <div id="newsletter-popup-success" class="nl-success">
                    <i class="fas fa-check-circle"></i>
                    <p>¡Gracias! Te has suscrito correctamente.</p>
                </div>

                <label class="nl-no-show" for="newsletter-popup-no-show-chk">
                    <input type="checkbox" id="newsletter-popup-no-show-chk">
                    No mostrar este popup de nuevo
                </label>
            </div>

        </div>
    </div>
</div>
