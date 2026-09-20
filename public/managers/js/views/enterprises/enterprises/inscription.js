Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formInscriptions');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: { required: true },
            'user[]': { required: true },
        },
        messages: {
            course: { required: 'Es necesario una opción.' },
            'user[]': { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var enterprise = $('#enterprise').val();
            var course = $('#course').val();
            var users = $('#users').val();

            formData.append('enterprises', enterprise);
            formData.append('users', users);
            formData.append('course', course);

            $.ajax({
                url: $form.data('generate-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
                            window.location.href = $form.data('navegation-url').replace(':slack', enterprise);
                        }, 2000);
                    } else {
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
