@extends('layouts.pages')

@section('title', 'Inicio')

@section('content')

<div class="conatct padding-top padding-bottom">
    <div class="container">
        <div class="contact__wrapper">
            <div class="row g-5">
                <div class="col-lg-6 aos-init aos-animate" data-aos="fade-right" data-aos-duration="800">
                    <div class="cotact__details">
                        <div class="contact__header">
                            <h2>Contacto para información</h2>
                            <p>Valoramos la retroalimentación de nuestros clientes y estamos aquí para responder a tus preguntas, escuchar tus
                                comentarios y proporcionar cualquier ayuda que necesites.</p>
                        </div>

                        <div class="contact__info ">
                            <div class="contact__item">
                                <div class="contact__item-thumb">
                                    <span><i class="fas fa-location-dot"></i></span>
                                </div>
                                <div class="contact__item-content">
                                    <h5>Ubicación</h5>
                                    <p class="mb-0">{{ setting("page_address") }}</p>
                                </div>
                            </div>
                            <div class="contact__item">
                                <div class="contact__item-thumb">
                                    <span>
                                        <i class="fas fa-phone"></i>
                                    </span>
                                </div>
                                <div class="contact__item-content">
                                    <h5>Número de contacto</h5>
                                    <p class="mb-0">+{{ setting("page_cellphone") }}</p>
                                </div>
                            </div>
                            <div class="contact__item">
                                <div class="contact__item-thumb">
                                    <span>
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <div class="contact__item-content">
                                    <h5>Correo electrónico</h5>
                                    <p class="mb-0">{{ setting("page_email") }}</p>
                                </div>
                            </div>
                            <div class="contact__item">
                                <div class="contact__item-thumb">
                                    <span><i class="fas fa-clock"></i></span>
                                </div>
                                <div class="contact__item-content">
                                    <h5>Horario</h5>
                                    <p class="mb-0">Lunes - Viernes: {{ setting("page_hour_weekend") }}</p>
                                    <p class="mb-0">Sabados: {{ setting("page_hour_weekends") }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 aos-init aos-animate d-flex align-items-center" data-aos="fade-left" data-aos-duration="800">
                    <div class="contact__form small-mt-50">
                        <h3>Contacto</h3>
                        <p>Si tienes dudas o quieres saber másnos podremos en contacto contigo.</p>

                            <form id="formContacts" class="account__form" enctype="multipart/form-data" role="form" onSubmit="return false">

                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Nombres" required="">
                                        </div>
                                        <label id="firstname-error" class="error d-none" for="firstname"></label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Apellidos" required="">
                                        </div>
                                        <label id="lastname-error" class="error d-none" for="lastname"></label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" required="">
                                        </div>
                                        <label id="email-error" class="error d-none" for="email"></label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" id="cellphone" name="cellphone" class="form-control" placeholder="Celular" required="">
                                        </div>
                                        <label id="cellphone-error" class="error d-none" for="cellphone"></label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <textarea name="message" id="message" placeholder="Ingresa su mensaje" class="form-control" rows="4" required=""></textarea>
                                        </div>
                                        <label id="message-error" class="error d-none" for="message"></label>
                                    </div>
                                </div>

                                <div class="account__form-passcheck">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms">
                                        <label for="terms" class="form-check-label">
                                            Acepta <a href="{{ route('terms') }}">términos y condiciones</a>.
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="errors d-none">
                                    </div>
                                </div>

                                <button type="button" id="submitContacts" class="btn btn-primary contact-disabled w-100">Enviar</button>

                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection






@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {

        jQuery.validator.addMethod("emailExt", function(value, element, param) {
            return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
        }, 'Porfavor ingrese email valido');

        jQuery.validator.addMethod(
            'validationTxt',
            function(value, element, param) {
                return value.match(
                    /^[ a-zA-ZñÑáéíóúÁÉÍÓÚ]+$/,
                )
            },
            'Por favor ingrese solo letras',
        )


        $("#terms").on("change", function() {
            value = $(this).is(":checked");
            if (value == true) {
                $('#submitContacts').removeClass("contact-disabled");
            } else {
                $('#submitContacts').addClass("contact-disabled");
            }
        });

        $("#submitContacts").click(function() {
            if ($(this).hasClass('contact-disabled')) return false;
            $("#formContacts").submit();
        });


        $("#formContacts").validate({
            submit: false,
            ignore: ".ignore",
            errorClass: 'error show-error',
            validClass: 'valid',
            rules: {
                firstname: {
                    validationTxt: true,
                    required: true,
                    minlength: 3,
                    maxlength: 30
                },
                lastname: {
                    validationTxt: true,
                    required: true,
                    minlength: 3,
                    maxlength: 30
                },
                email: {
                    required: true,
                    email: true,
                    emailExt: true
                },
                cellphone: {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 500
                },
                message: {
                    required: true,
                    minlength: 3,
                    maxlength: 8000
                }
            },
            messages: {
                firstname: {
                    text: 'Este campo es obligatorio.',
                    required: 'El campo nombre es necesario.',
                    minlength: 'El nombre debe contener al menos 3 caracteres.',
                    maxlength: 'El nombre  debe contener no mas de 30 caracteres'
                },
                lastname: {
                    text: 'Este campo es obligatorio.',
                    required: 'El campo nombre es necesario.',
                    minlength: 'El nombre debe contener al menos 3 caracteres.',
                    maxlength: 'El nombre  debe contener no mas de 30 caracteres'
                },
                email: {
                    required: "El email es necesario",
                    email: "Por favor ingrese email valido"
                },
                cellphone: {
                    required: "La celular es necesario",
                    minlength: "La celular debe contener al menos 6 caracteres",
                    maxlength: "La celular debe contener no mas de 20 caracteres",
                    number: "Sólo se pueden ingresar números"
                },
                message: {
                    required: 'Este campo es obligatorio.',
                    minlength: 'El mensaje debe contener al menos 3 caracteres.',
                    maxlength: 'El mensaje  debe contener no mas de 8000 caracteres',
                }
            },
            errorPlacement: function(error, element) {
                $("#" + element.attr("id") + "-error")
                    .removeClass("d-none")
                    .addClass("show-error")
                    .html(error.html());
            },
            submitHandler: function(form) {

                var $form = $('#formContacts');
                var formData = new FormData($form[0]);
                var firstname = $("#firstname").val();
                var lastname = $("#lastname").val();
                var email = $("#email").val();
                var cellphone = $("#cellphone").val();
                var message = $("#message").val();

                formData.append('firstname', firstname);
                formData.append('lastname', lastname);
                formData.append('email', email);
                formData.append('cellphone', cellphone);
                formData.append('message', message);

                var $submitBtn = $('#submitContacts');
                $submitBtn.addClass('contact-disabled');

                $.ajax({
                    url: "{{ route('contacts.store') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response) {

                        if(response.success == true){

                            toastr.success("¡Mensaje enviado correctamente! Nos pondremos en contacto pronto.", "Enviado", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right",
                                timeOut: 5000
                            });

                            setTimeout(function() {
                                $("#firstname").val('');
                                $("#lastname").val('');
                                $("#email").val('');
                                $("#cellphone").val('');
                                $("#message").val('');
                                $("#terms").prop('checked', false);
                                $submitBtn.addClass('contact-disabled');
                            }, 500);

                        } else {

                            if ($('#terms').is(':checked')) $submitBtn.removeClass('contact-disabled');

                            toastr.warning(response.message || "Hubo un problema al enviar el mensaje. Inténtalo de nuevo.", "Error", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });

                            $('.errors').text(response.message || '').removeClass('d-none');

                        }

                    },
                    error: function() {
                        if ($('#terms').is(':checked')) $submitBtn.removeClass('contact-disabled');
                        toastr.error("Error al enviar el mensaje. Por favor, inténtalo más tarde.", "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                });

            }

        });
    });
</script>

@endpush

