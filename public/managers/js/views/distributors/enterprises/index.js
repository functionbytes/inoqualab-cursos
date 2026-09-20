Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formEnterprises');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            'enterprises[]': { required: true },
        },
        messages: {
            'enterprises[]': { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var enterprises = $('#enterprises').val();

            formData.append('slack', slack);
            formData.append('enterprises', enterprises);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('update-url'),
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
                            window.location.href = $form.data('navegation-url').replace(':slack', slack);
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
