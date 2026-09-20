$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formCourses');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            enterprise: { required: true },
            'user[]': { required: true },
        },
        messages: {
            enterprise: { required: 'Es necesario una opción.' },
            'user[]': { required: 'Es necesario una opción.' },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            var slack = $('#slack').val();
            var enterprise = $('#enterprise').val();
            var users = $('#users').val();

            formData.append('slack', slack);
            formData.append('enterprise', enterprise);
            formData.append('users', users);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('updateUrl'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location.href = $form.data('redirectUrlTemplate').replace(':slack', response.enterprise);
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        $('.errors').text(response.message);
                        $('.errors').removeClass('d-none');
                    }
                }
            });
        }
    });
});
