$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }
});
