$(document).ready(function () {
    var $form = $('#formImportCourse');

    $form.validate({
        rules: { file: { required: true } },
        messages: { file: { required: 'Selecciona un archivo.' } },
        submitHandler: function (form) {
            var formData = new FormData(form);

            $.ajax({
                url: $form.data('import-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    window.location.href = response;
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Error al importar el archivo.');
                    }
                }
            });
        }
    });
});
