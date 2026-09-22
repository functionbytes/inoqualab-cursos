/**
 * Aplica custom properties CSS (--p, --pct, --thumb, --name-size...)
 * dinámicas a partir de atributos data-var-*, en vez de interpolar Blade
 * directamente dentro de un atributo style="" (ver .claude/rules/blade-
 * views.md — "NO style='' inline"). El CSS real (gradiente, transition,
 * background-size, font-size, etc.) vive en aula.css/panel.css/view.css vía
 * var(--p, 0%) / var(--thumb) / var(--name-size); acá solo se setea el
 * valor puntual de cada instancia (progreso de un curso, thumb de una
 * portada, tamaño de fuente del nombre en el certificado).
 *
 * Expuesto como window.applyDynamicStyleVars(scope) para poder re-aplicarlo
 * después de un reemplazo AJAX de HTML (ver views/courses/index.js).
 */
(function () {
    function apply(scope) {
        var root = scope || document;
        root.querySelectorAll('[data-var-p], [data-var-pct], [data-var-thumb], [data-var-name-size]').forEach(function (el) {
            if (el.dataset.varP !== undefined) {
                el.style.setProperty('--p', el.dataset.varP + '%');
            }
            if (el.dataset.varPct !== undefined) {
                el.style.setProperty('--pct', el.dataset.varPct + '%');
            }
            if (el.dataset.varThumb !== undefined && el.dataset.varThumb !== '') {
                el.style.setProperty('--thumb', "url('" + el.dataset.varThumb + "')");
            }
            if (el.dataset.varNameSize !== undefined) {
                el.style.setProperty('--name-size', el.dataset.varNameSize);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        apply(document);
    });

    window.applyDynamicStyleVars = apply;
})();
