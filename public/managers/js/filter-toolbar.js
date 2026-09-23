/**
 * FilterToolbar - wiring generico para el filtro de las tablas del panel
 * (ver managers.includes.filter-toolbar).
 *
 * Patron nuevo (popover anclado + chips): abre/cierra el popover, copia el
 * valor del campo marcado (radio/checkbox por nombre de grupo) a su input
 * hidden del form de busqueda, y envia el form al pulsar "Aplicar".
 *
 * Patron legacy (icon + modal Bootstrap): sigue funcionando igual que antes
 * con la misma API — solo cambia de donde lee el valor del control.
 *
 * Uso minimo en la vista (patron nuevo):
 *
 *   FilterToolbar.init({
 *       fields: { filterReviewed: 'popover_reviewed' },  // { hiddenInputId: nombre de grupo radio/checkbox }
 *   });
 *
 * Uso con un <select> dentro de un modal (patron legacy):
 *
 *   FilterToolbar.init({
 *       modal: '#filters-modal',
 *       fields: { filterReviewed: 'modalReviewed' },  // { hiddenInputId: id del <select> }
 *   });
 */
window.FilterToolbar = (function ($) {
    'use strict';

    function init(options) {
        var cfg = $.extend({
            popover: '#filters-popover',
            trigger: '#filters-trigger',
            modal: '#filters-modal',
            form: '#searchForm',
            applyBtn: '#applyFiltersBtn',
            fields: {},
        }, options);

        var $popover = $(cfg.popover);
        var $trigger = $(cfg.trigger);
        var usesPopover = $popover.length > 0 && $trigger.length > 0;

        function readFieldValue(name) {
            var $byName = $('[data-filter-name="' + name + '"]');
            if ($byName.length) {
                return $byName.filter(':checked').val() || '';
            }
            return $('#' + name).val() || '';
        }

        if (usesPopover) {
            var closePopover = function () {
                $popover.removeClass('is-open');
                $trigger.attr('aria-expanded', 'false');
            };

            // Los campos del popover usan data-filter-name en vez de name (para
            // no viajar como query param extra al enviar el <form> de busqueda),
            // asi que la exclusividad mutua de los radios se simula aqui.
            $popover.off('change.filterToolbarRadio').on(
                'change.filterToolbarRadio',
                'input[type="radio"][data-filter-name]',
                function () {
                    var name = $(this).attr('data-filter-name');
                    $popover.find('[data-filter-name="' + name + '"]').not(this).prop('checked', false);
                }
            );

            // Si no hay espacio debajo del boton para la altura completa del
            // popover (max-height en filter-toolbar.css), lo abre hacia arriba
            // en vez de hacia abajo — sin esto, un trigger cerca del borde
            // inferior de la ventana corta el popover contra el viewport
            // (managers.views.mails.index, con 5 campos, es el caso mas comun).
            //
            // Un popover con muchas opciones (ej. un filtro de Año con 8+
            // valores) puede ser mas alto que el espacio libre en AMBAS
            // direcciones cuando el trigger esta cerca del borde superior de
            // la ventana -- ahi el flip-up simple lo corta contra el propio
            // limite superior del viewport (visto en
            // supports.views.enterprises.courses.view, filtro de Año con
            // ~8 opciones + trigger justo debajo del header). Por eso ademas
            // de elegir la direccion con mas espacio, se clampea el
            // max-height real del popover a ese espacio disponible: el
            // overflow-y:auto de .filter-popover (filter-toolbar.css) hace
            // scroll interno en vez de desbordar el viewport.
            function positionPopover() {
                // Reset del max-height inline de una apertura anterior antes
                // de medir, para leer la altura natural (la de la clase CSS:
                // min(70vh, 560px)) y no la clampeada la ultima vez.
                $popover.css('max-height', '');
                var naturalHeight = $popover.outerHeight();

                var triggerRect = $trigger[0].getBoundingClientRect();
                var margin = 12;
                var spaceBelow = window.innerHeight - triggerRect.bottom - margin;
                var spaceAbove = triggerRect.top - margin;

                var fitsBelow = naturalHeight <= spaceBelow;
                var openUp = !fitsBelow && spaceAbove > spaceBelow;
                $popover.toggleClass('flip-up', openUp);

                var available = openUp ? spaceAbove : spaceBelow;
                if (naturalHeight > available) {
                    $popover.css('max-height', Math.max(160, available) + 'px');
                }
            }

            $trigger.off('click.filterToolbar').on('click.filterToolbar', function (e) {
                e.stopPropagation();
                var open = $popover.toggleClass('is-open').hasClass('is-open');
                $trigger.attr('aria-expanded', open ? 'true' : 'false');
                if (open) { positionPopover(); }
            });

            $popover.off('click.filterToolbar').on('click.filterToolbar', function (e) {
                e.stopPropagation();
            });

            $(document).off('click.filterToolbarOutside').on('click.filterToolbarOutside', closePopover);
            $(document).off('keydown.filterToolbarEsc').on('keydown.filterToolbarEsc', function (e) {
                if (e.key === 'Escape') { closePopover(); }
            });
        }

        $(cfg.applyBtn).off('click.filterToolbar').on('click.filterToolbar', function () {
            $.each(cfg.fields, function (hiddenId, controlName) {
                $('#' + hiddenId).val(readFieldValue(controlName));
            });
            if (usesPopover) {
                $popover.removeClass('is-open');
            } else {
                $(cfg.modal).modal('hide');
            }
            $(cfg.form).trigger('submit');
        });
    }

    return { init: init };
})(jQuery);
