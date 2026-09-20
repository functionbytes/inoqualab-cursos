Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formAction');

    $('.daterange').daterangepicker({
        startDate: $form.data('enroll-start'),
        endDate: $form.data('enroll-expire'),
        locale: { format: 'MM/DD/YYYY' }
    });

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            range: { required: true },
        },
        messages: {
            range: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var inscription = $('#inscription').val();
            var range = $('#range').val();

            formData.append('inscription', inscription);
            formData.append('range', range);

            $.ajax({
                url: $form.data('action-url'),
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
                            window.location.href = $form.data('redirect-url');
                        }, 2000);
                    } else {
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
