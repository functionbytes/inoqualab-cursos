$(document).ready(function () {
    var $config = $('#metas-config');
    var inlineBaseUrl = $config.data('inline-base-url');
    var bulkActionUrl = $config.data('bulk-action-url');

    $(document).on('click', '[data-action="reload"]', function () { window.location.reload(); });

    // .delete-btn vive dentro de #ajax-table-root (se recrea en cada carga
    // AJAX), por eso el bind es delegado en document en vez de directo.
    $(document).on('click', '.delete-btn', function () {
        $('#delete-modal .modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
    });

    $('#delete-form').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Eliminando...');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#delete-modal').modal('hide');
                toastr.success(res.message || 'Eliminado correctamente');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function () {
                toastr.error('Error al eliminar');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

    // #select-all vive dentro de #ajax-table-root y se recrea en cada carga
    // AJAX (buscar/filtrar/paginar), por eso BulkActions se re-inicializa vía
    // AjaxTable.init({ onLoaded: ... }) más abajo.
    function initMetasTable() {
        BulkActions.init({
            url: bulkActionUrl,
            entityLabel: 'meta(s) SEO',
        });

        FilterToolbar.init({
            fields: {
                filterSeoableType: 'popover_SeoableType',
                filterSortBy: 'popover_SortBy',
                filterSortDirection: 'popover_SortDirection',
            },
        });
    }

    initMetasTable();
    AjaxTable.init({ onLoaded: initMetasTable });

    // Inline edit
    $(document).on('click', '.editable-cell', function (e) {
        e.stopPropagation();
        if ($(this).find('input').length) return;
        const cell = $(this);
        const metaId = cell.data('meta-id');
        const field = cell.data('field');
        const currentValue = cell.data('original-value') || cell.find('.cell-text').first().text().trim();
        const input = $('<input type="text" class="form-control form-control-sm">').val(currentValue);
        input.on('blur keydown', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== 'Escape') return;
            if (e.key === 'Escape') { cell.find('.cell-text').show(); input.remove(); return; }
            const newValue = input.val();
            $.ajax({
                url: inlineBaseUrl + '/' + metaId + '/inline',
                method: 'PATCH',
                data: { _token: $('meta[name="csrf-token"]').attr('content'), field: field, value: newValue },
                success: function () {
                    cell.data('original-value', newValue);
                    cell.find('.cell-text').text(newValue || (field === 'description' ? 'Sin descripción' : 'Sin título'));
                    cell.find('.cell-text').show();
                    input.remove();
                    toastr.success('Actualizado');
                },
                error: function () {
                    toastr.error('Error al guardar');
                    cell.find('.cell-text').show();
                    input.remove();
                }
            });
        });
        cell.find('.cell-text').hide();
        cell.append(input);
        input.focus().select();
    });
});
