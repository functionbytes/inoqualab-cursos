Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#invoices-reports-index').data('config') || {};

    $('.daterange').daterangepicker();

    $("#formReport").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            method: {
                required: true,
            },
            condition: {
                required: true,
            },
            distributor: {
                required: true,
            },
            range: {
                required: true,
            },
        },
        messages: {
            method: {
                required: "Es necesario una opción.",
            },
            condition: {
                required: "Es necesario una opción.",
            },
            distributor: {
                required: "Es necesario una opción.",
            },
            range: {
                required: "Es necesario una opción.",
            },
        },
        submitHandler: function (form) {

            toastr.success("Se ha generado el reporte.", "Operación exitosa", {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });

            var query = {
                range: $("#range").val(),
                distributor: $("#distributor").val(),
                methods: $("#method").val(),
                condition: $("#condition").val(),
            }

            var url = config.routes.generate + "?" + $.param(query);

            window.location = url;
        }

    });

});
