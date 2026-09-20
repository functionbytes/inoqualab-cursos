Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#users-create').data('config') || {};

    $('#roles').change(function (e) {

        e.preventDefault();
        var role = $(this).val();

        if (role == 'enterprise') {
            $('.divEnterprise').removeClass("d-none");
        } else if (role == 'customer') {
            $('.divEnterprise').removeClass("d-none");
        } else {
            $('.divEnterprise').addClass("d-none");
        }

    });

    jQuery.validator.addMethod(
        'emailExt',
        function (value, element, param) {
            return value.match(
                /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
            )
        },
        'Porfavor ingrese email valido',
    );

    $("#formUsers").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            firstname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            lastname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            identification: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            cellphone: {
                required: false,
                number: true,
                minlength: 6,
                maxlength: 10,
            },
            email: {
                required: true,
                email: true,
                emailExt: true,
            },
            available: {
                required: true,
            },
            role: {
                required: true,
            },
            enterprise: {
                required: false,
            },
            address: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            password: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },

        },
        messages: {
            firstname: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            lastname: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            identification: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            cellphone: {
                required: "El parametro es necesario.",
                number: 'Solo se puede ingresar números.',
                minlength: "Debe contener al menos 6 caracter",
                maxlength: "Debe contener al menos 10 caracter",
            },
            email: {
                required: 'Tu email ingresar correo electrónico es necesario.',
                email: 'Por favor, introduce una dirección de correo electrónico válida.',
            },
            available: {
                required: "Es necesario un estado.",
            },
            role: {
                required: "Es necesario un estado.",
            },
            address: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            password: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 6 caracter",
                maxlength: "Debe contener al menos 10 caracter",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formUsers');
            var formData = new FormData($form[0]);
            var slack = $("#slack").val();
            var firstname = $("#firstname").val();
            var lastname = $("#lastname").val();
            var identification = $("#identification").val();
            var cellphone = $("#cellphone").val();
            var email = $("#email").val();
            var address = $("#address").val();
            var password = $("#password").val();
            var available = $("#available").val();
            var role = $("#roles").val();
            var enterprise = $("#enterprises").val();

            formData.append('slack', slack);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('identification', identification);
            formData.append('cellphone', cellphone);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('password', password);
            formData.append('available', available);
            formData.append('role', role);
            formData.append('enterprises', enterprise);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

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

                        message = response.message;

                        toastr.success(message, "Operación exitosa", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        setTimeout(function () {
                            window.location.href = config.routes.inscriptions.replace(':slack', response.slack);
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
