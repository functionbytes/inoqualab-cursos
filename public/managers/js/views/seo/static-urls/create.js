$(document).ready(function () {

    $('#formStaticUrl').validate({
        rules: {
            url: { required: true, maxlength: 2048 },
            priority: { required: true },
            changefreq: { required: true }
        },
        messages: {
            url: {
                required: 'La URL es obligatoria.',
                maxlength: 'Maximo 2048 caracteres.'
            },
            priority: { required: 'Selecciona una prioridad.' },
            changefreq: { required: 'Selecciona la frecuencia de cambio.' }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback d-block').insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
