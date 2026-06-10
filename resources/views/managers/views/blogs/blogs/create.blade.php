@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formBlogs" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="status" name="status" value="false">
                    <textarea style="display: none"  id="content" name="content"></textarea>
                    <textarea style="display: none"  id="description" name="description"></textarea>
                    <input type="hidden" id="thumbnail" name="thumbnail">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear noticias</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para permitirte  <mark><code>introducir</code></mark> nueva información de manera sencilla y estructurada. A continuación, se presentan varios campos que deberás completar con los datos requeridos.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  value="" placeholder="Ingresar titulo">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categoria</label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, null, ['class' => 'select2 form-control' , 'id' => 'categorie']) !!}
                                    </div>
                                    <label id="categorie-error" class="error d-none" for="categorie"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables,null, ['class' => 'select2 form-control', 'id' => 'available' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>              
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="date"  name="date"  value="" >
                                    </div>
                                    <label id="date-error" class="error d-none" for="date"></label>   
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Tags</label>
                                    <div class="input-group">
                                        {!! Form::select('tags[]', $tags, null, ['class' => 'select2 form-control'  , 'multiple' => 'multiple' , 'id' => 'tags']) !!}
                                    </div>
                                    <label id="tags-error" class="error d-none" for="tags"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Contenido</label>
                                    <div class="quill-wrapper">
                                        <div  id="descriptions"></div>
                                        <label id="description-error" class="error d-none" for="description"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Descripción</label>
                                    <div class="quill-wrapper">
                                        <div  id="contents"></div>
                                    </div>
                                    <label id="content-error" class="error d-none" for="content"></label>
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
                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')

    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function() {


            $("#formBlogs").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    date: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                    content: {
                        required: true,
                    },
                    categorie: {
                        required: true,
                    },
                    'tags[]': {
                        required: false,
                    },
                    available: {
                        required: true,
                    },
                    thumbnail: {
                        required: true
                    }

                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 1000 caracter",
                    },
                    date: {
                        required: "Es necesario una fecha.",
                    },
                    'tags[]': {
                        required: "Es necesario un tags.",
                    },
                    categorie: {
                        required: "Es necesario una categoria.",
                    },
                    available: {
                        required: "Es necesario un estado.",
                    },
                    description: {
                        required: "La descripción es necesario.",
                    },
                    content: {
                        required: "La contenido es necesario.",
                    },
                    thumbnail: {
                        required: "Es necesario una imagen.",
                    }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("id") == "thumbnail") {
                        error.insertAfter("#thumbnail");
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    var $form = $('#formBlogs');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var title = $("#title").val();
                    var date = $("#date").val();
                    var content = $("#content").val();
                    var description = $("#description").val();
                    var tags = $("#tags").val();
                    var available = $("#available").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('contents', content);
                    formData.append('description', description);
                    formData.append('date', date);
                    formData.append('tags', tags);
                    formData.append('available', available);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);
                    
                    $.ajax({
                        url: "{{ route('manager.blogs.store') }}",
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

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                myThumbnail.on("queuecomplete", function() {
                                    setTimeout(function() {
                                        window.location = "{{ route('manager.blogs') }}";
                                    }, 2000);
                                });

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
                url: "{{ route('manager.blogs.thumbnails') }}",
                method: 'POST',
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: "image/*", 
                parallelUploads: 1,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {
                   
                    var myDropzone = this;

                    item = $("#slack").val();

                    myDropzone.on("maxfilesexceeded", function(file) {
                        this.removeFile(file);
                    });

                    myDropzone.on('sending', function(file, xhr, formData) {
                        let blog = document.getElementById('slack').value;
                        formData.append('blog', blog);
                    });

                    myDropzone.on("addedfile", function(file) {
                        $("#thumbnail").val(file.name);
                        $("#formBlogs").validate().element("#thumbnail");
                    });

                    myDropzone.on("removedfile", function(file) {
                        $("#thumbnail").val('');
                        $("#formBlogs").validate().element("#thumbnail");
                        if (file.id) {
                            $.ajax({
                                type: 'GET',
                                url: "{{ route('manager.blogs.thumbnails.delete', ':id') }}".replace(':id', file.id),
                                success: function(result) {
                                    $("#status").val('false');
                                }
                            });
                        }
                    });

                    myDropzone.on('resetFiles', function() {
                        $("#status").val('false');
                        myDropzone.removeAllFiles();
                    });


                    myDropzone.on("success", function(file, response) {
                        $("#status").val('true');
                    });

                    myDropzone.on("queuecomplete", function() {
                      
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



        var content = new Quill('#contents', {
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        content.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        content.on('text-change', function(delta, oldDelta, source) {
            $('#content').val(content.container.firstChild.innerHTML);
        });


    </script>

@endpush

