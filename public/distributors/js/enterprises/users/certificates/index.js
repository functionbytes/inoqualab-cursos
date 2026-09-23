$(function () {
    FilterToolbar.init({
        fields: { filterCourse: 'popover_course' },
    });

    $(document).on('change', '.ajax-per-page-select', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });
});
