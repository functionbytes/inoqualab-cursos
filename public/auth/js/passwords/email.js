$(document).ready(function() {

    jQuery.validator.addMethod("emailExt", function(value, element, param) {
        return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
    }, 'Porfavor ingrese email valido');

    $("#formPassword").validate({
        submit: true,
        rules: {
            email: {
                required: true,
                email: true,
                emailExt: true
            },
        },
        messages: {
            email: {
                required: "El email es necesario",
                email: "Por favor ingrese email valido"
            },
        }
    });

});
