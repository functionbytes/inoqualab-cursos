$(document).ready(function () {

    var $form = $('#formCategories');
    var updateUrl = $form.data('update-url');
    var redirectUrl = $form.data('redirect-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            icon: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            available: {
                required: true,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            icon: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            available: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            formData.append('id', $('#id').val());
            formData.append('title', $('#title').val());
            formData.append('icon', $('#icon').val());
            formData.append('available', $('#available').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location = redirectUrl;
                        }, 3000);
                    } else {
                        $submitButton.prop('disabled', false);

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
});
