$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formInscriptions');
    var $submitButton = $('button[type="submit"]');

    $(document).on('click', '.registration', function () {
        var enterprise = $(this).data('enterprise');
        var course = $(this).data('course');
        var user = $(this).data('user');

        $.ajax({
            url: $form.data('enrollUrl'),
            type: 'POST',
            data: {
                enterprise: enterprise,
                course: course,
                user: user,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $('#error-modal').modal('hide');

                    toastr.success(response.message, 'Operación exitosa', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right'
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
            }
        });
    });

    $(document).on('change', '#user', function () {
        if ($submitButton.prop('disabled')) {
            $submitButton.prop('disabled', false);
        }
    });

    $(document).on('click', '.registration-close', function () {
        $('#user').val(0).trigger('change');
    });

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: { required: true },
            user: { required: true },
        },
        messages: {
            course: { required: 'Es necesario una opción.' },
            user: { required: 'Es necesario una opción.' },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            var enterprise = $('#enterprise').val();
            var course = $('#course').val();
            var users = $('#user').val();

            formData.append('enterprises', enterprise);
            formData.append('users', users);
            formData.append('course', course);

            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('storeUrl'),
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

                        $('#user').val(0).trigger('change');
                        $('#course').val(0).trigger('change');

                        $submitButton.prop('disabled', false);
                    } else {
                        var data = response['data'];

                        $('.registration')
                            .attr('data-enterprise', data['enterprise_enroll'])
                            .attr('data-course', data['course_enroll'])
                            .attr('data-user', data['customer_enroll']);

                        $('.identification').text(data['identification']);
                        $('.course').text(data['course']);
                        $('.start').text(data['enroll_start']);
                        $('.expire').text(data['enroll_expire']);

                        $('#error-modal').modal('show');
                    }
                }
            });
        }
    });
});
