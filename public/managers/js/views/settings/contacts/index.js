$(function () {
    var page = $('#contactsPage');

    var flashSuccess = page.data('flash-success');
    var flashError = page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filtros (popover) + bulk selection ──────────────────────────────────
    // Se re-ejecuta tras cada carga AJAX (buscar/filtrar/paginar) porque sus
    // binds son directos sobre el HTML de la tabla, que AjaxTable reemplaza.
    function initContactsTable() {
        FilterToolbar.init({
            fields: { filterReviewed: 'popover_reviewed' },
        });

        BulkActions.init({
            url: page.data('bulk-action-url'),
            entityLabel: 'contacto(s)',
        });
    }

    initContactsTable();

    // ── Búsqueda/filtro/paginación sin recargar la página ───────────────────
    AjaxTable.init({ onLoaded: initContactsTable });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
