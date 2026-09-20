$(document).ready(function () {
    Dropzone.autoDiscover = false;

    const $form = $('#formInscriptions');
    const enrollUrl = $form.data('enroll-url');
    const storeUrl = $form.data('store-url');

    $(document).on('click', '.registration', function () {
        const enterprise = $(this).data('enterprise');
        const course = $(this).data('course');
        const user = $(this).data('user');
        const $submitButton = $form.find('button[type="submit"]');

        $.ajax({
            url: enrollUrl,
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

    $(document).on('change', '#user', function () {
        const $submitButton = $form.find('button[type="submit"]');
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
            const formData = new FormData(form);
            const enterprise = $('#enterprise').val();
            const course = $('#course').val();
            const users = $('#user').val();

            formData.append('enterprises', enterprise);
            formData.append('users', users);
            formData.append('course', course);

            const $submitButton = $form.find('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: storeUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success === true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('#user').val(0).trigger('change');
                        $('#course').val(0).trigger('change');

                        $submitButton.prop('disabled', false);
                    } else {
                        const data = response['data'];

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
                },
            });
        },
    });
});
