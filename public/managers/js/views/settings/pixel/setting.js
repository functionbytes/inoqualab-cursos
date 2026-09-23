$(document).on('submit', '#formPixel', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formPixel').data('urls');

    $('#metaPixelEnable').on('change', function () {
        $('#metaPixelFields').toggleClass('d-none', !this.checked);
    });

    $('#formPixel').validate({
        submit: false,
        ignore: ':hidden',
        rules: {
            meta_pixel_id: {
                digits: true,
                maxlength: 50,
            },
        },
        messages: {
            meta_pixel_id: {
                digits: 'El ID solo puede contener números.',
                maxlength: 'Debe contener como máximo 50 caracteres',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formPixel');
            var formData = new FormData($form[0]);

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
