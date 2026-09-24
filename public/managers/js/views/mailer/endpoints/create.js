// Variables esperadas y mapeo: ver variables.js (compartido con edit).
$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    // Slug preview
    $('#slug').on('input', function () {
        $('#slugPreview').text($(this).val() || 'slug');
    });
});
