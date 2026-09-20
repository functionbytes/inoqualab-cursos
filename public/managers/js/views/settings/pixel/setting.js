$(document).on('submit', '#formPixel', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formPixel').data('urls');

    $('.form-check-input').click(function () {
        var check = $(this).prop('checked');
        $(this).prop('checked', check == true);
    });

    $('#formPixel').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            fb_pixel: {
                required: true,
                minlength: 1,
                maxlength: 100,
            },
        },
        messages: {
            fb_pixel: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formPixel');
            var formData = new FormData($form[0]);
            var fbPixel = $('#fb_pixel').val();
            var fbPixelEnable = $('#fb_pixel_enable').is(':checked');

            formData.append('fb_pixel', fbPixel);
            formData.append('fb_pixel_enable', fbPixelEnable);

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
