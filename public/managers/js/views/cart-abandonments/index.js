$(function () {

    var config = $('#cart-abandonments-index').data('config') || {};

    // ── Filtro de estado: envía el formulario al cambiar ──────────────────────
    $(document).on('change', '#status-filter', function () {
        this.form.submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'registro(s)',
    });

});
