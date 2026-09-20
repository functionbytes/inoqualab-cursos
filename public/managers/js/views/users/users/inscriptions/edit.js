Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#users-inscriptions-edit').data('config') || {};

    $('.daterange').daterangepicker({
        startDate: config.enrollStart,
        endDate: config.enrollExpire,
        locale: {
            format: 'DD/MM/YYYY' // Corregí el formato para que coincida con el orden típico español.
        }
    });


    $("#formAction").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            range: {
                required: true,
            },
        },
        messages: {
            range: {
                required: "Es necesario una opción.",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formAction');
            var formData = new FormData($form[0]);
            var inscription = $("#inscription").val();
            var range = $("#range").val();

            formData.append('inscription', inscription);
            formData.append('range', range);

            $.ajax({
                url: config.routes.action,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        message = response.message;

                        toastr.success(message, "Operación exitosa", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        setTimeout(function () {
                            window.location.href = config.routes.back;
                        }, 2000);

                    } else {

                        $submitButton.prop('disabled', false);
                        error = response.message;

                        toastr.warning(error, "Operación fallida", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        $('.errors').text(error);
                        $('.errors').removeClass('d-none');

                    }

                }
            });

        }

    });

});
