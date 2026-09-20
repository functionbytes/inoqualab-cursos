$(function () {
    var page = $('#roleFormPage');

    function refreshAssigned() {
        $('#assignedCount').text($('.permission-checkbox:checked').length);
    }

    // Filtro por nombre
    $('#permissionFilter').on('input', function () {
        var term = $(this).val().toLowerCase();
        $('.perm-item').each(function () {
            var label = $(this).find('.form-check-label').text().toLowerCase();
            $(this).toggle(term === '' || label.includes(term));
        });
        $('.perm-group').each(function () {
            $(this).toggle($(this).find('.perm-item:visible').length > 0);
        });
    });

    // Marcar/desmarcar todo un grupo
    $('.group-toggle').on('change', function () {
        $(this).closest('.perm-group').find('.permission-checkbox').prop('checked', this.checked);
        refreshAssigned();
    });

    $('.permission-checkbox').on('change', refreshAssigned);

    // Estado inicial de los toggles de grupo
    $('.perm-group').each(function () {
        var total = $(this).find('.permission-checkbox').length;
        var checked = $(this).find('.permission-checkbox:checked').length;
        $(this).find('.group-toggle').prop('checked', total > 0 && total === checked);
    });

    var flashSuccess = page.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess, 'Éxito');
    }
});
