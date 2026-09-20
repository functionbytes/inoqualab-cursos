$(document).on('submit', '#formMaintenance', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formMaintenance').data('urls');
    var secretLoaded = false;

    // La llave real nunca viaja en el HTML inicial: se trae via AJAX
    // (protegida por el mismo permiso) solo cuando hace falta.
    function loadSecret(callback) {
        if (secretLoaded) {
            callback();
            return;
        }

        $.ajax({
            url: urls.secret,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            success: function (response) {
                $('#maintenance_mode_value').val(response.value);
                secretLoaded = true;
                callback();
            }
        });
    }

    $('#maintenance_mode').click(function () {
        var check = $(this).prop('checked');
        if (check == true) {
            $('.maintenance_mode').removeClass('d-none');
            loadSecret(function () {});
        } else {
            $('.maintenance_mode').addClass('d-none');
        }
    });

    if ($('#maintenance_mode').is(':checked')) {
        loadSecret(function () {});
    }

    $('#btnToggleSecret').on('click', function () {
        loadSecret(function () {
            var input = $('#maintenance_mode_value');
            var icon = $('#eyeIconSecret');
            var isPassword = input.attr('type') === 'password';

            input.attr('type', isPassword ? 'text' : 'password');
            icon.toggleClass('fa-eye fa-eye-slash');
        });
    });

    $('#btnCopySecret').on('click', function () {
        loadSecret(function () {
            navigator.clipboard.writeText($('#maintenance_mode_value').val()).then(function () {
                toastr.info('Llave secreta copiada al portapapeles', '', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
            });
        });
    });

    $('#formMaintenance').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            maintenance_mode_value: {
                required: true,
                minlength: 1,
                maxlength: 200,
            },
        },
        messages: {
            maintenance_mode_value: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 4 caracter',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formMaintenance');
            var formData = new FormData($form[0]);
            var maintenanceModeValue = $('#maintenance_mode_value').val();
            var maintenanceMode = $('#maintenance_mode').is(':checked');

            formData.append('maintenance_mode', maintenanceMode);
            formData.append('maintenance_mode_value', maintenanceModeValue);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: urls.update,
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
                            window.location.href = urls.dashboard;
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
