$(function () {

    var $page = $('#courses-reviews-index');
    var config = $page.data('config') || {};

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initCoursesReviewsTable() {
        FilterToolbar.init({
        fields: { filterRating: 'popover_Rating' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'reseña(s)',
        deleteActions: ['delete'],
    });
    }

    initCoursesReviewsTable();

    AjaxTable.init({ onLoaded: initCoursesReviewsTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Ver detalle de la reseña ─────────────────────────────────────────────
    function setVisibilitySwitch(available) {
        $('#review-visibility-switch').prop('checked', !!available);
        $('#review-visibility-hint').text(
            available ? 'Se muestra en la pagina del curso' : 'Oculta: no aparece en la pagina del curso'
        );
    }

    $(document).on('click', '.btn-review-detail', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var rating = parseInt($btn.data('rating'), 10) || 0;
        var stars = '';
        for (var s = 1; s <= 5; s++) {
            stars += '<i class="fas fa-star' + (s <= rating ? '' : ' reviews-star-empty') + '"></i>';
        }

        var student = $btn.data('student');
        var available = parseInt($btn.data('available'), 10) === 1;

        $('#review-detail-course').text($btn.data('course'));
        $('#review-detail-student').text(student);
        $('#review-detail-avatar').text((student || '?').trim().charAt(0).toUpperCase());
        $('#review-detail-rating').html(stars);
        $('#review-detail-comment').text($btn.data('comment') || 'Sin comentario.');
        $('#review-detail-date').text($btn.data('date'));

        setVisibilitySwitch(available);
        $('#review-visibility-switch').data('url', $btn.data('toggleUrl'));

        $('#review-detail-modal').modal('show');
    });

    // ── Alternar visibilidad (switch dentro del modal) ───────────────────────
    $(document).on('change', '#review-visibility-switch', function () {
        var $switch = $(this);
        var url = $switch.data('url');
        if (!url) { return; }

        var previousState = !$switch.prop('checked');
        $switch.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message || 'Visibilidad actualizada');
                setVisibilitySwitch(res.available);
                $switch.prop('disabled', false);
                setTimeout(function () { location.reload(); }, 700);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al actualizar la visibilidad');
                $switch.prop('checked', previousState).prop('disabled', false);
            }
        });
    });

    // ── Alternar visibilidad (accion rapida desde la tabla) ──────────────────
    $(document).on('click', '.toggle-review-link', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (!url) { return; }

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message || 'Visibilidad actualizada');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al actualizar la visibilidad');
            }
        });
    });

});
