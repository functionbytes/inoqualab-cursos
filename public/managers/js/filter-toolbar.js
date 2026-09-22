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

            $trigger.off('click.filterToolbar').on('click.filterToolbar', function (e) {
                e.stopPropagation();
                var open = $popover.toggleClass('is-open').hasClass('is-open');
                $trigger.attr('aria-expanded', open ? 'true' : 'false');
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
