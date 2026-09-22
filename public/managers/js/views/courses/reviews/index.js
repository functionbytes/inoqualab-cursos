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
    $(document).on('click', '.btn-review-detail', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var rating = parseInt($btn.data('rating'), 10) || 0;
        var stars = '';
        for (var s = 1; s <= 5; s++) {
            stars += '<i class="fa-' + (s <= rating ? 'solid' : 'regular') + ' fa-star"></i>';
        }

        $('#review-detail-course').text($btn.data('course'));
        $('#review-detail-student').text($btn.data('student'));
        $('#review-detail-rating').html(stars);
        $('#review-detail-comment').text($btn.data('comment') || '—');
        $('#review-detail-date').text($btn.data('date'));
        $('#review-detail-modal').modal('show');
    });

});
