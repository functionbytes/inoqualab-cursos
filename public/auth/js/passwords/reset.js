$(document).ready(function() {

    $("#formPassword").validate({
        submit: true,
        rules: {
            password: {
                required: true,
                minlength: 3
            },
            password_confirmation: {
                required: true,
                minlength: 3,
                equalTo: "#password"
            },
        },
        messages: {
            password: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            password_confirmation: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
                equalTo: "Por favor, introduzca el mismo valor de nuevo."
            },
        }
    });

});
