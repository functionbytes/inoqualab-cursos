$(document).ready(function () {

    var config = $('#orders-edit').data('config') || {};

    $(".datepicker").datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Establecer la fecha en el datepicker
    $('.datepicker').datepicker('setDate', config.paymentDate);

    $("#formOrders").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            condition: {
                required: true,
            },
            method: {
                required: true,
            },
            payment: {
                required: function () {
                    return $("#condition").val() == 4 ? true : false
                },
            },
        },
        messages: {
            condition: {
                required: "Es necesario un estado.",
            },
            method: {
                required: "Es necesario un estado.",
            },
            payment: {
                required: "Es necesario una fecha.",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formOrders');
            var formData = new FormData($form[0]);
            var slack = $("#slack").val();
            var condition = $("#condition").val();
            var method = $("#method").val();
            var payment = $("#payment").val();

            formData.append('slack', slack);
            formData.append('condition', condition);
            formData.append('methods', method);
            formData.append('payment', payment);

            $.ajax({
                url: config.routes.update,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        var url = config.routes.view.replace(':id', response.data.slack);

                        $("#view-modal").modal("show");
                        $("#view-link").attr("href", url);

                    } else {

                        toastr.warning("Se ha generado un error.", "Operación fallida", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        error = response.message;
                        $('.errors').removeClass('d-none');
                        $('.errors').removeClass('d-none');
                    }

                }
            });

        }

    });

});
