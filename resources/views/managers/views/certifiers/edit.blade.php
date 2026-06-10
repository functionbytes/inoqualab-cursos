@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCertifier" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="description" name="description" value="{{ $certifier->description }}">
                    <input type="hidden" id="id" name="id" value="{{ $certifier->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $certifier->slack }}">
                    <input type="hidden" id="statuSignatures" name="statuSignatures" value="{{ $signature }}">
                    <input type="hidden" id="statuThumbnails" name="statuThumbnails" value="{{ $thumbnail }}">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">
                    <input type="hidden" id="signature" name="signature">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Foto</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="thumbnail">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Firma</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la firma de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="signature">
                            <div class="fallback">
                                <input type="file" hidden name="signature">
                            </div>
                        </div>
                        <label id="signature-error" class="error d-none" for="signature"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar capacitador</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $certifier->firstname }}" placeholder="Ingresar nombres">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $certifier->lastname }}" placeholder="Ingresar apellido">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Identificación</label>
                                        <input type="text" class="form-control" id="identification"  name="identification" value="{{ $certifier->identification }}" placeholder="Ingresar identificación">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Profección</label>
                                        <input type="text" class="form-control" id="profession"  name="profession" value="{{ $certifier->profession }}" placeholder="Ingresar profección">
                                    </div>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $certifier->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>



                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="col-form-label">Descripción</label>
                                        <div class="quill-wrapper">
                                            <div id="descriptions">{!! $certifier->description !!}</div>
                                        </div>
                                        <label id="description-error" class="error d-none" for="description"></label>
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

            $("#formCertifier").validate({
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
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    profession: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    available: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                    thumbnail: {
                        required: function() {
                            return $("#statuThumbnails").val() == "false" ? true : false;
                        }
                    },
                    signature: {
                        required: function() {
                            return $("#statuSignatures").val() == "false" ? true : false;
                        }
                    }
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
                    profession: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    available: {
                        required: "Es necesario un estado.",
                    },
                    description: {
                        required: "La descripción es necesario.",
                    },
                    thumbnail: {
                        required: "Es necesario una imagen.",
                    },
                    signature: {
                        required: "Es necesario una firma.",
                    }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("id") == "thumbnail") {
                        error.insertAfter("#thumbnail");
                    } else {
                        error.insertAfter(element);
                    }
                    if (element.attr("id") == "signature") {
                        error.insertAfter("#signature");
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    var $form = $('#formCertifier');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var firstname = $("#firstname").val();
                    var lastname = $("#lastname").val();
                    var identification = $("#identification").val();
                    var profession = $("#profession").val();
                    var description = $("#description").val();
                    var available = $("#available").val();

                    formData.append('slack', slack);
                    formData.append('firstname', firstname);
                    formData.append('lastname', lastname);
                    formData.append('identification', identification);
                    formData.append('profession', profession);
                    formData.append('description', description);
                    formData.append('available', available);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.certifiers.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                slack = response.slack;
                                $("#slack").val(slack);
                                myThumbnail.processQueue();
                                mySignature.processQueue();

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                myThumbnail.on("queuecomplete", function() {
                                    
                                });

                                mySignature.on("queuecomplete", function() {
                                    
                                });

                                setTimeout(function() {
                                     window.location = "{{ route('manager.certifiers') }}";
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

            var myThumbnail = new Dropzone("div#thumbnail", {
                paramName: "file",
                url: "{{ route('manager.certifiers.thumbnails') }}",
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: ".jpg, .jpeg",
                parallelUploads: 1,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {

                    var myThumbnail = this;

                    item = $("#slack").val();


                    $.getJSON("{{ route('manager.certifiers.thumbnails.get', ':item') }}".replace(':item', item), function(data) {

                        $.each(data, function(key, value) {

                            var mockFile = {
                                id: value.id,
                                uuid: value.uuid,
                                name: value.file,
                                size: value.size,
                                path: value.path,
                                file: value.file
                            };

                            myThumbnail.options.addedfile.call(myThumbnail, mockFile);
                            myThumbnail.options.thumbnail.call(myThumbnail, mockFile,  value.path);
                            myThumbnail.options.complete.call(myThumbnail, mockFile);
                            myThumbnail.options.success.call(myThumbnail, mockFile);

                        });

                    });

                    myThumbnail.on("maxfilesexceeded", function(file) {
                        this.removeFile(file);
                    });

                    myThumbnail.on('sending', function(file, xhr, formData) {
                        let certifier = document.getElementById('slack').value;
                        formData.append('certifier', certifier);
                    });

                    myThumbnail.on("addedfile", function(file) {
                        $("#thumbnail").val(file.name);
                        $("#formCertifier").validate().element("#thumbnail");
                    });

                    myThumbnail.on("removedfile", function(file) {

                        $("#thumbnail").val('');
                        $("#formCertifier").validate().element("#thumbnail");

                        if (file.id) {
                            $.ajax({
                                type: 'GET',
                                url: "{{ route('manager.settings.metadata.delete', ':id') }}".replace(':id', file.id),
                                success: function(result) {
                                    $("#statuThumbnails").val('false');
                                }
                            });
                        }

                    });

                    myThumbnail.on('resetFiles', function() {
                        $("#statuThumbnails").val('false');
                        myThumbnail.removeAllFiles();
                    });

                    myThumbnail.on("success", function(file, response) {
                        $("#statuThumbnails").val('true');
                    });

                    myThumbnail.on("queuecomplete", function() {
                    });

                    myThumbnail.on("complete", function() {
                    });
                }
            });

            var mySignature = new Dropzone("div#signature", {
                paramName: "file",
                url: "{{ route('manager.certifiers.signatures') }}",
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: ".png",
                parallelUploads: 1,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {

                    var mySignature = this;

                    item = $("#slack").val();

                    $.getJSON("{{ route('manager.certifiers.signatures.get', ':item') }}".replace(':item', item), function(data) {

                        $.each(data, function(key, value) {

                            var mockFile = {
                                id: value.id,
                                uuid: value.uuid,
                                name: value.file,
                                size: value.size,
                                path: value.path,
                                file: value.file
                            };

                            mySignature.options.addedfile.call(mySignature, mockFile);
                            mySignature.options.thumbnail.call(mySignature, mockFile,  value.path);
                            mySignature.options.complete.call(mySignature, mockFile);
                            mySignature.options.success.call(mySignature, mockFile);

                        });

                    });


                    mySignature.on("maxfilesexceeded", function(file) {
                        this.removeFile(file);
                    });

                    mySignature.on('sending', function(file, xhr, formData) {
                        let certifier = document.getElementById('slack').value;
                        formData.append('certifier', certifier.replace('"',''));
                    });

                    mySignature.on("addedfile", function(file) {
                        $("#signature").val(file.name);
                        $("#formCertifier").validate().element("#thumbnail");
                    });

                    mySignature.on("removedfile", function(file) {

                        $("#signature").val('');
                        $("#formCertifier").validate().element("#signature");

                        if (file.id) {
                            $.ajax({
                                type: 'GET',
                                url: "{{ route('manager.certifiers.signatures.delete', ':id') }}".replace(':id', file.id),
                                success: function(result) {
                                    $("#statuSignatures").val('false');
                                }
                            });
                        }


                    });

                    mySignature.on('resetFiles', function() {
                        $("#statuSignatures").val('false');
                        mySignature.removeAllFiles();
                    });


                    mySignature.on("success", function(file, response) {
                        $("#statuSignatures").val('true');
                    });

                    mySignature.on("queuecomplete", function() {
                    });

                    mySignature.on("complete", function() {
                    });
                }
            });

        });


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
                toolbar: toolbarOption,
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
            $('#description').val(text);
        });

    </script>

@endpush



