@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formSetting" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}


                    <textarea class="d-none" type="hidden"  id="page_description" name="page_description">{!! clean(setting('page_description'), 'content') !!}</textarea>
                    <textarea class="d-none" type="hidden"  id="page_politic" name="page_politic">{!! clean(setting('page_politic'), 'content') !!}</textarea>
                    <textarea class="d-none" type="hidden"  id="page_term" name="page_term">{!! clean(setting('page_term'), 'content') !!}</textarea>
                    <input  type="hidden" id="page_logo" name="page_logo" value="{{ setting('page_logo') }}">
                    <input  type="hidden" id="page_favicon" name="page_favicon" value="{{ setting('page_favicon') }}">
                    <input  type="hidden" id="id" name="id" value="{{ $setting->id }}">
                    <input  type="hidden" id="statuLogo" name="statuLogo" value="{{ $logo }}">
                    <input  type="hidden" id="statuFavicon" name="statuFavicon" value="{{ $favicon}}">
                    <input  type="hidden" id="statuEdit" name="statuEdit" value="false">
                    <input  type="hidden" id="favicon" name="favicon">
                    <input  type="hidden" id="logo" name="logo">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Logo</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="logo">
                            <div class="fallback">
                                <input type="file" hidden name="logo">
                            </div>
                        </div>
                        <label id="logo-error" class="error d-none" for="logo"></label>
                    </div>


                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Favicon</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="favicon">
                            <div class="fallback">
                                <input type="file" hidden name="favicon">
                            </div>
                        </div>
                        <label id="favicon-error" class="error d-none" for="favicon"></label>
                    </div>
                    

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar certificador</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Configura los datos generales del sitio: información de contacto, redes sociales, políticas, términos y preferencias del portal.
                        </p>

                        <div class="row">

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Nombres</label>
                                                <input type="text" class="form-control" id="page_title"  name="page_title" value="{{ setting('page_title') }}" placeholder="Ingresar titulo">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Copyright</label>
                                                <input type="text" class="form-control" id="page_copyright"  name="page_copyright" value="{{ setting('meta_title')  }}" placeholder="Ingresar copyright">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Correo electronico</label>
                                                <input type="text" class="form-control" id="page_email"  name="page_email" value="{{ setting('page_email')  }}" placeholder="Ingresar correo electronico">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Dirección</label>
                                                <input type="text" class="form-control" id="page_address"  name="page_address" value="{{ setting('page_address')  }}" placeholder="Ingresar nombres">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Telefono</label>
                                                <input type="text" class="form-control" id="page_phone"  name="page_phone" value="{{ setting('page_phone')  }}" placeholder="Ingresar telefono">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Celular</label>
                                                <input type="text" class="form-control" id="page_cellphone"  name="page_cellphone" value="{{ setting('page_cellphone')  }}" placeholder="Ingresar celular">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Whatsapp</label>
                                                <input type="text" class="form-control" id="page_whatsapp"  name="page_whatsapp" value="{{ setting('page_whatsapp')  }}" placeholder="Ingresar whatsapp">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Facebook</label>
                                                <input type="text" class="form-control" id="social_media_facebook"  name="social_media_facebook" value="{{ setting('social_media_facebook')  }}" placeholder="Ingresar facebook">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Instagram</label>
                                                <input type="text" class="form-control" id="social_media_instagram"  name="social_media_instagram" value="{{ setting('social_media_instagram')  }}" placeholder="Ingresar instragram">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Linkedin</label>
                                                <input type="text" class="form-control" id="social_media_linkedin"  name="social_media_linkedin" value="{{ setting('social_media_linkedin')  }}" placeholder="Ingresar linkedin">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Twitter</label>
                                                <input type="text" class="form-control" id="social_media_twitter"  name="social_media_twitter" value="{{ setting('social_media_twitter')  }}" placeholder="Ingresar twitter">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Youtube</label>
                                                <input type="text" class="form-control" id="social_media_youtube"  name="social_media_youtube" value="{{ setting('social_media_youtube')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Mapa</label>
                                                <input type="text" class="form-control" id="page_map"  name="page_map" value="{{ setting('page_map')  }}" placeholder="Ingresar mapa">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Horario entre semana</label>
                                                <input type="text" class="form-control" id="page_hour_weekend" name="page_hour_weekend"
                                                    value="{{ setting('page_hour_weekend')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>


                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Horario fines de semana</label>
                                                <input type="text" class="form-control" id="page_hour_weekends" name="page_hour_weekends"
                                                    value="{{ setting('page_hour_weekends')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>



                                    <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Politicas de privacidad</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="politics">{!! clean(setting('page_politic'), 'content') !!}</div>
                                            </div>
                                            <label id="politic-error" class="error d-none" for="politic"></label>
                                        </div>
                                    </div>

                                     <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Terminos y condiciones</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="terms">{!! clean(setting('page_term'), 'content') !!}</div>
                                            </div>
                                            <label id="term-error" class="error d-none" for="term"></label>
                                        </div>
                                    </div>

                                     <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Descripción</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="descriptions">{!! clean(setting('page_description'), 'content') !!}</div>
                                            </div>
                                            <label id="description-error" class="error d-none" for="description"></label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Reseñas de estudiantes</label>
                                            <select class="form-select" id="reviews_enabled" name="reviews_enabled">
                                                <option value="1" {{ setting('reviews_enabled') == 1 ? 'selected' : '' }}>Habilitadas — los alumnos pueden calificar</option>
                                                <option value="0" {{ setting('reviews_enabled') == 1 ? '' : 'selected' }}>Deshabilitadas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está habilitada, al aprobar el examen de un curso el estudiante podrá calificarlo con estrellas y dejar un comentario que se mostrará en la página del curso.</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Notificaciones del formulario de contacto</label>
                                            <select class="form-select" id="contact_notifications" name="contact_notifications">
                                                <option value="1" {{ setting('contact_notifications') == '1' ? 'selected' : '' }}>Habilitadas — enviar email al recibir un mensaje</option>
                                                <option value="0" {{ setting('contact_notifications') != '1' ? 'selected' : '' }}>Deshabilitadas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está habilitado, cada mensaje del formulario de contacto dispara un email de alerta al correo configurado en "Correo electrónico".</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Registro público de usuarios</label>
                                            <select class="form-select" id="registration_enabled" name="registration_enabled">
                                                <option value="1" {{ setting('registration_enabled') != '0' ? 'selected' : '' }}>Habilitado — cualquier visitante puede registrarse</option>
                                                <option value="0" {{ setting('registration_enabled') == '0' ? 'selected' : '' }}>Deshabilitado — solo el administrador crea cuentas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está deshabilitado, el formulario de registro en la web pública rechaza nuevas inscripciones.</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Diseño del aula (portal del estudiante)</label>
                                            <select class="form-select" id="aula_version" name="aula_version">
                                                <option value="1" {{ setting('aula_version') == '2' ? '' : 'selected' }}>Versión 1 — contenido a la izquierda, temario a la derecha</option>
                                                <option value="2" {{ setting('aula_version') == '2' ? 'selected' : '' }}>Versión 2 — temario lateral izquierdo, contenido a la derecha</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Define cómo ven los alumnos el aula y las clases de cada curso.</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="action-form border-top mt-4">
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                                    Guardar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                     </div>

                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')

    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function() {


            jQuery.validator.addMethod(
                'emailExt',
                function (value, element, param) {
                    return value.match(
                        /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
                    )
                },
                'Porfavor ingrese email valido',
            );


            $("#formSetting").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    page_title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    page_copyright: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    page_address: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    page_whatsapp: {
                        required: false,
                        number: true,
                        minlength: 6,
                        maxlength: 12,
                    },
                    page_cellphone: {
                        required: false,
                        number: true,
                        minlength: 6,
                        maxlength: 12,
                    },
                    page_phone: {
                        required: false,
                        number: true,
                        minlength: 6,
                        maxlength: 12,
                    },
                    page_email: {
                        required: true,
                        email: true,
                        emailExt: true,
                    },
                    social_media_facebook: {
                        required: false,
                        url : true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    social_media_instagram: {
                        required: false,
                        url : true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    social_media_linkedin: {
                        required: false,
                        url : true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    social_media_twitter: {
                        required: false,
                        url : true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    social_media_youtube: {
                        required: false,
                        url : true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    page_map: {
                        required: false,
                        minlength: 3,
                        maxlength: 2000,
                    },
                    page_description: {
                        required: false,
                    },
                    page_term: {
                        required: false,
                    },
                    page_politic: {
                        required: false,
                    },
                    logo: {
                        required: function() {
                            return $("#statuLogo").val() == 'true' ? false : true;
                        }
                    },
                    favicon: {
                        required: function() {
                            return $("#statuFavicon").val() == 'true' ? false : true;
                        }
                    }
                },
                messages: {
                    page_title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    page_map: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    page_copyright: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    page_address: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    social_media_facebook: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    social_media_linkedin: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    social_media_instagram: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    social_media_twitter: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    social_media_youtube: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    page_whatsapp: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        minlength: "Debe contener al menos 6 caracter",
                        maxlength: "Debe contener al menos 12 caracter",
                    },
                    page_cellphone: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        minlength: "Debe contener al menos 6 caracter",
                        maxlength: "Debe contener al menos 12 caracter",
                    },
                    page_phone: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        minlength: "Debe contener al menos 6 caracter",
                        maxlength: "Debe contener al menos 12 caracter",
                    },
                    page_email: {
                        required: 'Tu email ingresar correo electrónico es necesario.',
                        email: 'Por favor, introduce una dirección de correo electrónico válida.',
                    },
                    page_description: {
                        required: "La descripción es necesario.",
                    },
                    page_term: {
                        required: "La descripción es necesario.",
                    },
                    page_politic: {
                        required: "La descripción es necesario.",
                    },
                    logo: {
                        required: "Es necesario una imagen.",
                    },
                    favicon: {
                        required: "Es necesario una imagen.",
                    }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("id") == "favicon") {
                        error.insertAfter("#favicon");
                    } else if (element.attr("id") == "logo") {
                        error.insertAfter("#logo");
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    var $form = $('#formSetting');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var page_title = $("#page_title").val();
                    var page_copyright = $("#page_copyright").val();
                    var page_email = $("#page_email").val();
                    var page_phone = $("#page_phone").val();
                    var page_cellphone = $("#page_cellphone").val();
                    var page_whatsapp = $("#page_whatsapp").val();
                    var page_address = $("#page_address").val();
                    var social_media_facebook = $("#social_media_facebook").val();
                    var social_media_instagram = $("#social_media_instagram").val();
                    var social_media_twitter = $("#social_media_twitter").val();
                    var social_media_youtube = $("#social_media_youtube").val();
                    var social_media_linkedin = $("#social_media_linkedin").val();
                    var page_description = $("#page_description").val();
                    var page_term = $("#page_term").val();
                    var page_politic = $("#page_politic").val();
                    var page_map = $("#page_map").val();
                    var page_hour_weekend = $("#page_hour_weekend").val();
                    var page_hour_weekends = $("#page_hour_weekends").val();
                    var reviews_enabled = $("#reviews_enabled").val();
                    var contact_notifications = $("#contact_notifications").val();
                    var registration_enabled = $("#registration_enabled").val();
                    var aula_version = $("#aula_version").val();

                    formData.append('reviews_enabled', reviews_enabled);
                    formData.append('contact_notifications', contact_notifications);
                    formData.append('registration_enabled', registration_enabled);
                    formData.append('aula_version', aula_version);
                    formData.append('slack', slack);
                    formData.append('page_title', page_title);
                    formData.append('page_copyright', page_copyright);
                    formData.append('page_email', page_email);
                    formData.append('page_phone', page_phone);
                    formData.append('page_cellphone', page_cellphone);
                    formData.append('page_whatsapp', page_whatsapp);
                    formData.append('page_address', page_address);
                    formData.append('page_cellphone', page_cellphone);
                    formData.append('page_description', page_description);
                    formData.append('page_term', page_term);
                    formData.append('page_politic', page_politic);
                    formData.append('page_map', page_map);
                    formData.append('page_hour_weekend', page_hour_weekend);
                    formData.append('page_hour_weekends', page_hour_weekends);
                    formData.append('social_media_facebook', social_media_facebook);
                    formData.append('social_media_instagram', social_media_instagram);
                    formData.append('social_media_twitter', social_media_twitter);
                    formData.append('social_media_youtube', social_media_youtube);
                    formData.append('social_media_linkedin', social_media_linkedin);


                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);


                    $.ajax({
                        url: "{{ route('manager.settings.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                myLogo.processQueue();
                                myFavicon.processQueue();

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                myLogo.on("queuecomplete", function() {
                                    
                                });

                                myFavicon.on("queuecomplete", function() {
                                    
                                });

                                setTimeout(function() {
                                    window.location = "{{ route('manager.settings') }}";
                                }, 2000);

                            }else{

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


            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var myLogo = new Dropzone("div#logo", {
                paramName: "file",
                url: "{{ route('manager.settings.logo') }}",
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: ".png, .svg",
                parallelUploads: 1,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {

                    var myLogo = this;

                    item = $("#page_logo").val();

                    $.getJSON("{{ route('manager.settings.logo.get', ':item') }}".replace(':item', item), function(data) {

                        $.each(data, function(key, value) {
                            
                             $("#statuLogo").val('true');

                            var mockFile = {
                                id: value.id,
                                uuid: value.uuid,
                                name: value.file,
                                size: value.size,
                                path: value.path,
                                file: value.file
                            };

                            myLogo.options.addedfile.call(myLogo, mockFile);
                            myLogo.options.thumbnail.call(myLogo, mockFile,  value.path);
                            myLogo.options.complete.call(myLogo, mockFile);
                            myLogo.options.success.call(myLogo, mockFile);

                        });

                    });

                    myLogo.on("maxfilesexceeded", function(file) {
                        this.removeFile(file);
                    });

                    myLogo.on('sending', function(file, xhr, formData) {
                        let setting = document.getElementById('page_logo').value;
                        formData.append('setting', setting);
                    });

                    myLogo.on("addedfile", function(file) {
                        $("#logo").val(file.name);
                        $("#formSetting").validate().element("#logo");
                    });

                    myLogo.on("removedfile", function(file) {

                        $("#logo").val('');
                        $("#formSetting").validate().element("#logo");

                        if (file.id) {
                            $.ajax({
                                type: 'DELETE',
                                url: "{{ route('manager.settings.logo.delete', ':id') }}".replace(':id', file.id),
                                success: function(result) {
                                    $("#status").val('false');
                                }
                            });
                        }


                    });

                    myLogo.on('resetFiles', function() {
                        $("#statuLogo").val('false');
                        myLogo.removeAllFiles();
                    });

                    myLogo.on("success", function(file, response) {
                        $("#statuLogo").val('true');
                    });

                    myLogo.on("queuecomplete", function() {
                    });

                    myLogo.on("complete", function() {
                    });
                }
            });

            var myFavicon = new Dropzone("div#favicon", {
                paramName: "file",
                url: "{{ route('manager.settings.favicon') }}",
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: ".png, .svg, .favicon",
                parallelUploads: 1,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {

                    var myFavicon = this;

                    item = $("#page_favicon").val();

                    $.getJSON("{{ route('manager.settings.favicon.get', ':item') }}".replace(':item', item), function(data) {

                        $.each(data, function(key, value) {


                            $("#statuFavicon").val('true');

                            var mockFile = {
                                id: value.id,
                                uuid: value.uuid,
                                name: value.file,
                                size: value.size,
                                path: value.path,
                                file: value.file
                            };

                            myFavicon.options.addedfile.call(myFavicon, mockFile);
                            myFavicon.options.thumbnail.call(myFavicon, mockFile,  value.path);
                            myFavicon.options.complete.call(myFavicon, mockFile);
                            myFavicon.options.success.call(myFavicon, mockFile);

                        });

                    });


                    myFavicon.on("maxfilesexceeded", function(file) {
                        this.removeFile(file);
                    });

                    myFavicon.on('sending', function(file, xhr, formData) {
                        let setting = document.getElementById('page_favicon').value;
                        formData.append('setting', setting);
                    });

                    myFavicon.on("addedfile", function(file) {
                        $("#favicon").val(file.name);
                        $("#formSetting").validate().element("#favicon");
                    });

                    myFavicon.on("removedfile", function(file) {

                        $("#favicon").val('');
                        $("#formSetting").validate().element("#favicon");

                        if (file.id) {
                            $.ajax({
                                type: 'DELETE',
                                url: "{{ route('manager.settings.favicon.delete', ':id') }}".replace(':id', file.id),
                                success: function(result) {
                                    $("#status").val('false');
                                }
                            });
                        }

                    });

                    myFavicon.on('resetFiles', function() {
                        $("#statuFavicon").val('false');
                        myFavicon.removeAllFiles();
                    });

                    myFavicon.on("success", function(file, response) {
                        $("#statuFavicon").val('true');
                    });

                    myFavicon.on("queuecomplete", function() {
                    });

                    myFavicon.on("complete", function() {
                    });
                }
            });

        });

    </script>



    <script type="text/javascript">


        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],        
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],              
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],     
            [{ 'indent': '-1' }, { 'indent': '+1' }],          
            [{ 'direction': 'rtl' }],                        
            [{ 'size': ['small', false, 'large', 'huge'] }], 
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [ 'link', 'image', 'video' ],
            [{ 'color': [] }, { 'background': [] }],         
            [{ 'font': [] }],
            [{ 'align': [] }],

            ['clean']                                        
        ];

       
        var toolbarOption = [
            ['clean']                                        
        ];

        var description = new Quill('#descriptions', {

            modules: {
                toolbar: toolbarOptions,
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        description.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        description.on('text-change', function(delta, oldDelta, source) {

            var text = description.container.firstChild.innerHTML.replaceAll("<p><br></p>", "");
            $('#page_description').val(text);
        });



        var term = new Quill('#terms', {

            modules: {
                toolbar: toolbarOptions,
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        term.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        term.on('text-change', function(delta, oldDelta, source) {

            var text = term.container.firstChild.innerHTML.replaceAll("<p><br></p>", "");
            $('#page_term').val(text);
        });



        var politic = new Quill('#politics', {

            modules: {
                toolbar: toolbarOptions,
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        politic.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        politic.on('text-change', function(delta, oldDelta, source) {

            var text = politic.container.firstChild.innerHTML.replaceAll("<p><br></p>", "");
            $('#page_politic').val(text);
        });




    </script>

@endpush



