$(document).ready(function () {

    var config = $('#invoices-create').data('config') || {};

    $('.daterange').daterangepicker();

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
            distributor: {
                required: true,
            },

        },
        messages: {
            condition: {
                required: "Es necesario un estado.",
            },
            method: {
                required: "Es necesario un estado.",
            },
            distributor: {
                required: "Es necesario un distribuidor.",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formInvoices');
            var formData = new FormData($form[0]);
            var distributor = $("#distributor").val();
            var condition = $("#condition").val();
            var method = $("#method").val();
            var range = $("#range").val();

            formData.append('distributor', distributor);
            formData.append('condition', condition);
            formData.append('methods', method);
            formData.append('range', range);

            $.ajax({
                url: config.routes.store,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        var url = config.routes.view.replace(':id', response.data);

                        $("#view-modal").modal("show");
                        $("#view-link").attr("href", url);

                    } else {
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
