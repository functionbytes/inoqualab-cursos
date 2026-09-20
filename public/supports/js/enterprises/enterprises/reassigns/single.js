$(document).ready(function () {
    Dropzone.autoDiscover = false;

    const $form = $('#formCourses');
    const reassignUrl = $form.data('reassign-url');
    const redirectUrlTemplate = $form.data('redirect-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            enterprise: { required: true },
        },
        messages: {
            enterprise: { required: 'Es necesario una opción.' },
        },
        submitHandler: function (form) {
            const formData = new FormData(form);
            const slack = $('#slack').val();
            const enterprise = $('#enterprise').val();

            formData.append('slack', slack);
            formData.append('enterprise', enterprise);

            const $submitButton = $form.find('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: reassignUrl,
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

                        setTimeout(function () {
                            window.location.href = redirectUrlTemplate.replace(':slack', response.enterprise);
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').text(response.message).removeClass('d-none');
                    }
                },
            });
        },
    });
});
