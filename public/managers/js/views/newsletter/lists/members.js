$(function () {
    var $addButton = $('#btnAddMember');

    $addButton.on('click', function () {
        var email = $.trim($('#memberEmail').val());
        if (!email) {
            toastr.warning('El email es obligatorio.');
            return;
        }

        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: $addButton.data('add-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { email: email, name: $.trim($('#memberName').val()) },
            success: function (response) { window.location.href = response.redirect; },
            error: function (xhr) {
                $btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON) {
                    var errors = xhr.responseJSON.errors || {};
                    $.each(errors, function (field, messages) { toastr.error(messages[0]); });
                } else {
                    toastr.error('No se pudo agregar el suscriptor.');
                }
            },
        });
    });

    $(document).on('click', '.btn-remove-member', function () {
        var $btn = $(this);
        var url = $btn.data('url');

        $btn.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message || 'Suscriptor retirado.');
                $btn.closest('tr').fadeOut(200, function () { $(this).remove(); });
            },
            error: function () {
                $btn.prop('disabled', false);
                toastr.error('No se pudo retirar al suscriptor.');
            },
        });
    });
});
