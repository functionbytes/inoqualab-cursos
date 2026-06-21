<style>
#newsletter-popup-modal .modal-dialog { max-width: 440px; }
#newsletter-popup-modal .modal-content {
    border: none;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,.22);
}
#newsletter-popup-modal .nl-accent {
    background: #081A28;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 11px 24px;
}
#newsletter-popup-modal .nl-body { padding: 24px 28px 28px; }
#newsletter-popup-modal .nl-title {
    font-size: 22px;
    font-weight: 700;
    color: #081A28;
    line-height: 1.3;
    margin: 0 0 12px;
    font-family: 'Poppins', Helvetica, Arial, sans-serif;
}
#newsletter-popup-modal .nl-desc {
    font-size: 14px;
    color: #5A7093;
    line-height: 1.6;
    margin: 0 0 22px;
}
#newsletter-popup-modal .nl-label {
    font-size: 14px;
    font-weight: 600;
    color: #081A28;
    margin-bottom: 6px;
    display: block;
}
#newsletter-popup-modal .nl-label .req { color: #008bce; margin-left: 2px; }
#newsletter-popup-modal .nl-input {
    width: 100%;
    border: 1px solid #D8E0E8;
    border-radius: 4px;
    padding: 11px 14px;
    font-size: 14px;
    color: #081A28;
    outline: none;
    transition: border-color .2s;
    margin-bottom: 16px;
    box-sizing: border-box;
    font-family: 'Open Sans', Helvetica, Arial, sans-serif;
}
#newsletter-popup-modal .nl-input:focus { border-color: #008bce; }
#newsletter-popup-modal .nl-input::placeholder { color: #aab5c2; }
#newsletter-popup-modal .nl-btn {
    display: block;
    width: 100%;
    background: #081A28;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 14px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .2s;
    font-family: 'Poppins', Helvetica, Arial, sans-serif;
    margin-top: 6px;
}
#newsletter-popup-modal .nl-btn:hover { background: #008bce; }
#newsletter-popup-modal .nl-no-show {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 18px;
    font-size: 13px;
    color: #5A7093;
    font-width: 500;
    cursor: pointer;
    user-select: none;
}
#newsletter-popup-modal .nl-no-show input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #008bce;
    flex-shrink: 0;
}
#newsletter-popup-modal .nl-error {
    font-size: 13px;
    color: #dc3545;
    margin: -10px 0 14px;
    display: none;
}
#newsletter-popup-modal .nl-success {
    text-align: center;
    padding: 20px 0 10px;
    display: none;
}
#newsletter-popup-modal .nl-success i {
    font-size: 40px;
    color: #27ae60;
    display: block;
    margin-bottom: 12px;
}
#newsletter-popup-modal .nl-success p {
    font-size: 16px;
    font-weight: 700;
    color: #081A28;
    margin: 0;
}
#newsletter-popup-modal .nl-close {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 22px;
    color: rgba(255,255,255,.7);
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    z-index: 10;
    padding: 0;
}
#newsletter-popup-modal .nl-close:hover { color: #fff; }
</style>

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
