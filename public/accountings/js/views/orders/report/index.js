Dropzone.autoDiscover = false;

$(document).on('submit', '#formReport', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    const $form = $('#formReport');
    const getEnterprisesUrl = $form.data('get-enterprises-url');
    const generateUrl = $form.data('generate-url');

    $('#distributor').on('change', function () {
        $.ajax({
            url: getEnterprisesUrl,
            type: 'POST',
            data: {
                distributor: $(this).val(),
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (data) {
                $('#enterprise').empty().select2({
                    data: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text,
                        };
                    }),
                    placeholder: 'Seleccionar una empresa',
                });
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            },
        });
    });

    $('#distributors').select2({
        placeholder: 'Seleccionar una distribuidor',
        minimumResultsForSearch: Infinity,
    });

    $('.daterange').daterangepicker();

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            distributor: {
                required: true,
            },
            enterprise: {
                required: function () {
                    return $('#distributor').val() != '0';
                },
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
                required: 'Es necesario una opción.',
            },
            enterprise: {
                required: 'Es necesario una opción.',
            },
            type: {
                required: 'Es necesario una opción.',
            },
            condition: {
                required: 'Es necesario una opción.',
            },
            method: {
                required: 'Es necesario una opción.',
            },
            range: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function () {
            toastr.success('Se ha generado el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right',
            });

            const query = {
                range: $('#range').val(),
                distributor: $('#distributor').val(),
                enterprise: $('#enterprise').val(),
                type: $('#type').val(),
                methods: $('#method').val(),
                condition: $('#condition').val(),
            };

            window.location = generateUrl + '?' + $.param(query);
        },
    });
});
