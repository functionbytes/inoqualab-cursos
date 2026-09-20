$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formContacs');
    var updateUrl = $form.data('update-url');
    var redirectUrl = $form.data('redirect-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            available: {
                required: true,
            },
        },
        messages: {
            reviewed: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            formData.append('slack', $('#slack').val());
            formData.append('reviewed', $('#reviewed').val());

            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente la solicitud de contacto.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 3000);
                    } else {
                        toastr.warning('Se ha generado un error.', 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').removeClass('d-none').html(response.message);
                    }
                },
            });
        },
    });

    var description = new Quill('#messages', {
        modules: {
            toolbar: [['clean']],
            clipboard: { matchVisual: false },
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow',
    });

    $('.ql-editor').addClass('disabled').attr('contenteditable', false);
});
