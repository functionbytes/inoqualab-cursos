$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ── Toggle active state ──────────────────────────────────────────────────
    $(document).on('change', '.toggle-active', function () {
        var $toggle = $(this);

        $.ajax({
            url: $toggle.data('url'),
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                toastr.success(res.message ?? 'Estado actualizado.');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al actualizar el estado.');
                $toggle.prop('checked', !$toggle.prop('checked'));
            }
        });
    });

    // ── Apply to metas ───────────────────────────────────────────────────────
    var currentApplyData = null;

    $(document).on('click', '.apply-btn', function (e) {
        e.preventDefault();
        var previewUrl = $(this).data('preview-url');
        var applyUrl = $(this).data('apply-url');
        var templateId = $(this).data('id');

        currentApplyData = { applyUrl: applyUrl, templateId: templateId };

        $('#apply-modal-body').html(
            '<div class="py-3">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 text-muted">Calculando registros afectados...</p>' +
            '</div>'
        );
        $('#confirm-apply-btn').prop('disabled', true);
        $('#applyModal').modal('show');

        $.getJSON(previewUrl, function (res) {
            var count = res.affected_count ?? 0;
            $('#apply-modal-body').html(
                '<div class="display-4 text-primary mb-3"><i class="fas fa-layer-group"></i></div>' +
                '<p class="mb-1">Esta plantilla se aplicará a</p>' +
                '<h3 class="fw-bold mb-1">' + count + '</h3>' +
                '<p class="text-muted mb-0">registro(s) de meta SEO.</p>'
            );
            $('#confirm-apply-btn').prop('disabled', count === 0);
        }).fail(function () {
            $('#apply-modal-body').html('<p class="text-danger py-3">Error al cargar la previsualización.</p>');
        });
    });

    $('#confirm-apply-btn').on('click', function () {
        if (!currentApplyData) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Aplicando...');

        $.ajax({
            url: currentApplyData.applyUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ template_id: currentApplyData.templateId }),
            success: function (res) {
                $('#applyModal').modal('hide');
                toastr.success(res.message ?? 'Plantilla aplicada correctamente.');
                currentApplyData = null;
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al aplicar la plantilla.');
                $btn.prop('disabled', false).text('Aplicar');
            }
        });
    });

    $('#applyModal').on('hidden.bs.modal', function () {
        currentApplyData = null;
        $('#confirm-apply-btn').prop('disabled', true).text('Aplicar');
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    function initSeoTemplatesTable() {
        BulkActions.init({
        url: $('#bulk-config').data('bulk-url'),
        entityLabel: 'plantilla(s)',
    });
    }

    initSeoTemplatesTable();

    AjaxTable.init({ onLoaded: initSeoTemplatesTable });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
