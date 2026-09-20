$(document).ready(function () {

    $('.select2').select2({ width: '100%' });

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let currentApplyData = null;

    function openApplyModal(previewUrl, applyUrl, templateId) {
        currentApplyData = { applyUrl, templateId };

        $('#apply-modal-body').html(`
            <div class="py-3">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Calculando registros afectados...</p>
            </div>
        `);
        $('#confirm-apply-btn').prop('disabled', true).text('Aplicar');
        $('#applyModal').modal('show');

        $.getJSON(previewUrl, function (res) {
            const count = res.affected_count ?? 0;
            $('#apply-modal-body').html(`
                <div class="display-4 text-primary mb-3"><i class="fas fa-layer-group"></i></div>
                <p class="mb-1">Esta plantilla se aplicará a</p>
                <h3 class="fw-bold mb-1">${count}</h3>
                <p class="text-muted small mb-0">registro(s) de meta SEO.</p>
            `);
            $('#confirm-apply-btn').prop('disabled', count === 0);
        }).fail(function () {
            $('#apply-modal-body').html('<p class="text-danger py-3">Error al cargar la previsualización.</p>');
        });
    }

    // Sidebar apply button
    $('.apply-sidebar-btn').on('click', function () {
        openApplyModal($(this).data('preview-url'), $(this).data('apply-url'), $(this).data('id'));
    });

    // Confirm apply
    $('#confirm-apply-btn').on('click', function () {
        if (!currentApplyData) return;
        const $btn = $(this);
        $btn.prop('disabled', true).text('Aplicando...');

        $.ajax({
            url: currentApplyData.applyUrl,
            method: 'POST',
            data: JSON.stringify({ template_id: currentApplyData.templateId }),
            contentType: 'application/json',
            success: function (res) {
                $('#applyModal').modal('hide');
                toastr.success(res.message || 'Plantilla aplicada correctamente.');
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
        $('#confirm-apply-btn').text('Aplicar');
    });

});
