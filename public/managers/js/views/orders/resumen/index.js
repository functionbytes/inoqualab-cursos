Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#orders-resumen-index').data('config') || {};

    $('#distributor').on('change', function () {
        var distributorId = $(this).val();

        $.ajax({
            url: config.routes.getEnterprises,
            type: 'POST',
            data: {
                distributor: distributorId
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                $('#enterprise').empty().select2({
                    data: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    }),
                    placeholder: "Seleccionar una empresa"
                });
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            }
        });
    });

    $("#distributors").select2({
        placeholder: "Seleccionar una distribuidor",
        minimumResultsForSearch: Infinity
    });

    $('.daterange').daterangepicker();

    $("#formReport").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            distributor: {
                required: true,
            },
            enterprise: {
                required: function (element) {
                    return $("#distributor").val() == '0' ? false : true;
                }
            },
            type: {
                required: true,
            },
            condition: {
                required: true,
            },
            method: {
                required: true,
            },
            range: {
                required: true,
            },
        },
        messages: {
            distributor: {
                required: "Es necesario una opción.",
            },
            enterprise: {
                required: "Es necesario una opción.",
            },
            type: {
                required: "Es necesario una opción.",
            },
            condition: {
                required: "Es necesario una opción.",
            },
            method: {
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
                enterprise: $("#enterprise").val(),
                type: $("#type").val(),
                methods: $("#method").val(),
                condition: $("#condition").val(),
            }

            var url = config.routes.generate + "?" + $.param(query);

            window.location = url;
        }
    });

});
