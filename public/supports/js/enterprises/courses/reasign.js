$(document).ready(function () {
    const $form = $('#formReasign');

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
            const formData = new FormData(form);

            $.ajax({
                url: $form.data('reasign-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function () {
                    window.location.href = $form.data('redirect-url');
                },
                error: function () {
                    toastr.error('Error al procesar la reasignación.');
                },
            });
        },
    });
});
