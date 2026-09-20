Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formInscriptions');

    $('.registration').on('click', function () {
        var enterprise = $(this).data('enterprise');
        var course = $(this).data('course');
        var user = $(this).data('user');

        var $submitButton = $('button[type="submit"]');

        $.ajax({
            url: $form.data('enroll-url'),
            type: 'POST',
            data: {
                enterprise: enterprise,
                course: course,
                user: user,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.success) {
                    $('#error-modal').modal('hide');

                    toastr.success(response.message, 'Operación exitosa', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });

                    $('.registration').attr('data-enterprise', '').attr('data-course', '').attr('data-user', '');

                    $('#user').val(0).trigger('change');
                    $('#course').val(0).trigger('change');
                    $submitButton.prop('disabled', false);
                } else {
                    toastr.error('Error al inscribir al usuario.');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            },
        });
    });

    $('#user').on('change', function (e) {
        var $submitButton = $('button[type="submit"]');
        if ($submitButton.prop('disabled')) {
            $submitButton.prop('disabled', false);
        }
    });

    $('.registration-close').on('click', function () {
        $('#user').val(0).trigger('change');
    });

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: {
                required: true,
            },
            user: {
                required: true,
            },
        },
        messages: {
            course: {
                required: 'Es necesario una opción.',
            },
            user: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('enterprises', $('#enterprise').val());
            formData.append('users', $('#user').val());
            formData.append('course', $('#course').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('store-url'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
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
                            positionClass: 'toast-bottom-right',
                        });

                        $('#user').val(0).trigger('change');
                        $('#course').val(0).trigger('change');

                        $submitButton.prop('disabled', false);
                    } else {
                        var data = response['data'];
                        var enterprise = data['enterprise_enroll'];
                        var course = data['course_enroll'];
                        var slack = data['customer_enroll'];

                        $('.registration').attr('data-enterprise', enterprise).attr('data-course', course).attr('data-user', slack);

                        $('.identification').text(data['identification']);
                        $('.course').text(data['course']);
                        $('.start').text(data['enroll_start']);
                        $('.expire').text(data['enroll_expire']);

                        $('#error-modal').modal('show');
                    }
                },
            });
        },
    });
});
