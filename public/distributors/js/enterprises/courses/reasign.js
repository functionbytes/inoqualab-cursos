$(document).ready(function () {
    var $form = $('#formReasign');

    $form.validate({
        rules: {
            course: { required: true },
            'user[]': { required: true },
        },
        messages: {
            course: { required: 'Selecciona un curso.' },
            'user[]': { required: 'Selecciona al menos un usuario.' },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            $.ajax({
                url: $form.data('updateUrl'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function () {
                    window.location.href = $form.data('redirectUrl');
                },
                error: function () {
                    toastr.error('Error al procesar la reasignación.');
                }
            });
        }
    });
});
