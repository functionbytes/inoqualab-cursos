$(document).ready(function () {

    var config = $('#invoices-edit').data('config') || {};

    $(".datepicker").datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Establecer la fecha en el datepicker
    $('.datepicker').datepicker('setDate', config.paymentDate);

    $("#formInvoices").validate({
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
                required: "Es necesario una fecha",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formInvoices');
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

                    var distributor = response.data.distributor;

                    if (response.success == true) {

                        toastr.success("Se ha editado correctamente la factura.", "Operación exitosa", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        setTimeout(function () {
                            window.location.href = config.routes.distributorInvoices.replace('SLACK_PLACEHOLDER', distributor);
                        }, 2000);
                    } else {

                        toastr.warning(response.error, "Operación fallida", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                    }
                }
            });

        }

    });

});
