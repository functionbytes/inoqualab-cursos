/**
 * BulkActions - selección múltiple + acción en lote para tablas del panel.
 *
 * Requiere en el HTML de la vista:
 *   - checkbox de cabecera:      id="select-all"
 *   - checkbox por fila:         class="bulk-checkbox" value="{{ $item->id }}"
 *   - barra flotante + modal:    @include('managers.includes.bulk-toolbar-modal', [...])
 *
 * Uso mínimo en la vista:
 *
 *   BulkActions.init({
 *       url: '{{ route('manager.courses.bulk-action') }}',
 *       entityLabel: 'curso(s)',
 *   });
 */
window.BulkActions = (function ($) {
    'use strict';

    function init(options) {
        var cfg = $.extend({
            checkbox: '.bulk-checkbox',
            selectAll: '#select-all',
            toolbar: '#bulk-toolbar',
            modal: '#bulk-modal',
            actionSelect: '#bulk-action-select',
            applyBtn: '#btn-bulk-apply',
            entityLabel: 'registro(s)',
            url: null,
            deleteActions: ['delete'],
            idsParam: 'ids',
        }, options);

        var $selectAll = $(cfg.selectAll);
        var $toolbar = $(cfg.toolbar);
        var $applyBtn = $(cfg.applyBtn);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        function getChecked() {
            return $(cfg.checkbox + ':checked').map(function () { return $(this).val(); }).get();
        }

        function sync() {
            var total = $(cfg.checkbox).length;
            var checked = $(cfg.checkbox + ':checked').length;

            $selectAll.prop('indeterminate', checked > 0 && checked < total);
            $selectAll.prop('checked', total > 0 && checked === total);

            // Actualiza todos los contadores visibles (barra flotante + modal),
            // no solo el que vive dentro de la barra.
            $('[data-bulk-count]').text(checked);
            checked > 0 ? $toolbar.removeClass('d-none') : $toolbar.addClass('d-none');
        }

        $selectAll.off('change.bulk').on('change.bulk', function () {
            $(cfg.checkbox).prop('checked', this.checked);
            sync();
        });

        $(document).off('change.bulk', cfg.checkbox).on('change.bulk', cfg.checkbox, sync);

        $(cfg.modal).off('hide.bs.modal.bulk').on('hide.bs.modal.bulk', function () {
            $(cfg.actionSelect).val('');
            $applyBtn.prop('disabled', false).text('Aplicar');
        });

        $applyBtn.off('click.bulk').on('click.bulk', function () {
            var action = $(cfg.actionSelect).val();
            var ids = getChecked();

            if (!action) { toastr.warning('Selecciona una acción.'); return; }
            if (!ids.length) { toastr.warning('Selecciona al menos un ' + cfg.entityLabel.replace('(s)', '') + '.'); return; }

            if (cfg.deleteActions.indexOf(action) !== -1
                && !confirm('¿Eliminar los ' + ids.length + ' ' + cfg.entityLabel + '?')) {
                return;
            }

            $applyBtn.prop('disabled', true).text('Procesando...');

            var payload = { action: action };
            payload[cfg.idsParam] = ids;

            $.ajax({
                url: cfg.url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    $(cfg.modal).modal('hide');
                    toastr.success(res.message || 'Acción aplicada.');
                    setTimeout(function () { location.reload(); }, 700);
                },
                error: function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al procesar.');
                    $applyBtn.prop('disabled', false).text('Aplicar');
                },
            });
        });

        return {
            sync: sync,
            getChecked: getChecked,
            reset: function () {
                $(cfg.checkbox).prop('checked', false);
                $selectAll.prop({ checked: false, indeterminate: false });
                $toolbar.addClass('d-none').find('[data-bulk-count]').text(0);
            },
        };
    }

    return { init: init };
})(jQuery);
