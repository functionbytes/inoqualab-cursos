$(document).ready(function () {
    var $form = $('#formReassign');

    $form.validate({
        rules: { enterprise: { required: true } },
        messages: { enterprise: { required: 'Selecciona una empresa.' } },
        submitHandler: function (form) {
            $.ajax({
                url: $form.data('reassignUrl'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                data: $(form).serialize(),
                success: function () {
                    toastr.success('Usuario reasignado correctamente.');
                    setTimeout(function () {
                        window.location.href = $form.data('redirectUrl');
                    }, 1000);
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (k, v) {
                            toastr.error(v[0]);
                        });
                    } else {
                        toastr.error('Error al reasignar el usuario.');
                    }
                }
            });
        }
    });
});
