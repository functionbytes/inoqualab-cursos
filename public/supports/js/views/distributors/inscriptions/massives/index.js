let errorQueue = [];

$(document).ready(function () {
    var $form = $('#formInscriptions');

    $('#enterprise').select2({
        minimumResultsForSearch: 0,
    });

    $('#courses').select2({
        minimumResultsForSearch: 0,
    });

    $(document).on('click', '.registration', function () {
        var enterprise = $(this).data('enterprise');
        var course = $(this).data('course');
        var user = $(this).data('user');
        var distributor = $(this).data('distributor');

        $.ajax({
            url: $form.data('enroll-url'),
            type: 'POST',
            data: {
                enterprise: enterprise,
                distributor: distributor,
                course: course,
                user: user,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message, 'Operación exitosa', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });

                    errorQueue.shift();
                } else {
                    toastr.error(response.message || 'Error al inscribir al usuario.');
                }
                showNextError();
                $('#error-modal').modal('hide');
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                $('#error-modal').modal('hide');
            },
        });
    });

    $(document).on('click', '.registration-close', function () {
        errorQueue.shift();
        $('#error-modal').modal('hide');
    });

    $('#error-modal').on('hidden.bs.modal', function () {
        setTimeout(() => {
            showNextError();
        }, 300);
    });

    function showNextError() {
        $('.registration').attr('data-enterprise', '').attr('data-course', '').attr('data-user', '');

        if (errorQueue.length === 0) {
            $('#error-modal').modal('hide');

            $('#users').val(0).trigger('change');
            $('#courses').val(0).trigger('change');
            $('#enterprise').val(0).trigger('change');

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', false);
            return;
        }

        let error = errorQueue[0];

        $('.registration')
            .attr('data-enterprise', error.enterprise_enroll)
            .attr('data-course', error.course_enroll)
            .attr('data-user', error.customer_enroll);

        $('.customer').text(error.customer_name || 'N/A');
        $('.course').text(error.course || 'N/A');
        $('.start').text(error.enroll_start || 'N/A');
        $('.expire').text(error.enroll_expire || 'N/A');

        setTimeout(() => {
            $('#error-modal').modal('show');
        }, 300);
    }

    $('#enterprise').on('change', function () {
        var enterpriseId = $(this).val();

        $.ajax({
            url: $form.data('get-users-url'),
            type: 'POST',
            data: {
                enterprise: enterpriseId,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (data) {
                $('#users')
                    .empty()
                    .select2({
                        data: data.map(function (item) {
                            return {
                                id: item.id,
                                text: item.text,
                            };
                        }),
                        placeholder: 'Seleccionar usuarios',
                    });
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            },
        });

        $.ajax({
            url: $form.data('get-courses-url'),
            type: 'POST',
            data: {
                enterprise: enterpriseId,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (data) {
                $('#courses')
                    .empty()
                    .select2({
                        data: data.map(function (item) {
                            return {
                                id: item.id,
                                text: item.text,
                            };
                        }),
                        placeholder: 'Seleccionar un curso',
                    });
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

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: {
                required: true,
            },
            enterprises: {
                required: true,
            },
            users: {
                required: true,
            },
        },
        messages: {
            course: {
                required: 'Es necesario una opción.',
            },
            enterprises: {
                required: 'Es necesario una opción.',
            },
            users: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('distributor', $('#distributor').val());
            formData.append('enterprise', $('#enterprise').val());
            formData.append('courses', $('#courses').val());
            formData.append('users', $('#users').val());

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

                        $('#users').val(0).trigger('change');
                        $('#courses').val(0).trigger('change');
                        $('#enterprise').val(0).trigger('change');

                        $submitButton.prop('disabled', false);
                    } else if (response.errors && response.errors.length > 0) {
                        errorQueue = [...response.errors];
                        showNextError();
                    }
                },
            });
        },
    });
});
