$(document).ready(function () {
    var $form = $('#formInscriptions');
    var $submitButton = $('button[type="submit"]');

    $('#enterprise').select2({ minimumResultsForSearch: 0 });
    $('#course').select2({ minimumResultsForSearch: 0 });

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

    $('#enterprise').on('change', function () {
        var enterpriseId = $(this).val();

        $.ajax({
            url: $form.data('getUsersUrl'),
            type: 'POST',
            data: { enterprise: enterpriseId },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $('#user').empty().select2({
                    data: data.map(function (item) {
                        return { id: item.id, text: item.text };
                    }),
                    placeholder: 'Seleccionar usuarios'
                });
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            }
        });

        $.ajax({
            url: $form.data('getCoursesUrl'),
            type: 'POST',
            data: { enterprise: enterpriseId },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $('#course').empty().select2({
                    data: data.map(function (item) {
                        return { id: item.id, text: item.text };
                    }),
                    placeholder: 'Seleccionar un curso'
                });
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            }
        });
    });

    $('#user').on('change', function () {
        if ($submitButton.prop('disabled')) {
            $submitButton.prop('disabled', false);
        }
    });

    $(document).on('click', '.registration-close', function () {
        $('#user').val(0).trigger('change');
        $('#course').val(0).trigger('change');
    });

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: { required: true },
            enterprise: { required: true },
            user: { required: true },
        },
        messages: {
            course: { required: 'Es necesario una opción.' },
            enterprise: { required: 'Es necesario una opción.' },
            user: { required: 'Es necesario una opción.' },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            formData.append('enterprise', $('#enterprise').val());
            formData.append('user', $('#user').val());
            formData.append('distributor', $('#distributor').val());

            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('storeUrl'),
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

                        $('#user').val(0).trigger('change');
                        $('#course').val(0).trigger('change');
                        $('#enterprise').val(0).trigger('change');

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
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);
                    toastr.error('Error al procesar la solicitud. Por favor intente de nuevo.', 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right'
                    });
                }
            });
        }
    });
});
