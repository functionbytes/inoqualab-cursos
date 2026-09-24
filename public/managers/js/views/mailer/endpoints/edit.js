// Variables esperadas y mapeo: ver variables.js (compartido con create).
$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    // Progress bar (width depends on the endpoint's success rate)
    var $successBar = $('#successRateBar');
    $successBar.css('width', $successBar.data('width') + '%');

    // Copy token
    $('#copyTokenBtn').on('click', function () {
        const token = $('#tokenInput').val();
        navigator.clipboard.writeText(token).then(function () {
            toastr.success('Token copiado al portapapeles');
        });
    });
});
