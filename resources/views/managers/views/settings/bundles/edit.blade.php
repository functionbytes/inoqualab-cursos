@extends('layouts.managers')

@section('content')

@php
    $selectedCourses = $bundle->courses->pluck('id')->toArray();
@endphp

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formBundles" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $bundle->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $bundle->slack }}">
                    <textarea class="d-none" id="meta_description" name="meta_description">{!! clean($bundle->meta_description, 'content') !!}</textarea>
                    <textarea class="d-none" id="description" name="description">{!! clean($bundle->description, 'content') !!}</textarea>
                    <input type="hidden" id="status" name="status" value="{{ $thumbnail }}">
                    <input type="hidden" id="edit" name="edit" value="true">
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
                            <h5 class="mb-0">Crear paquete</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos del paquete. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="{{ $bundle->title }}"  placeholder="Ingresar titulo">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Precio</label>
                                        <input type="text" class="form-control" id="price"  name="price" value="{{ $bundle->price }}"  placeholder="Ingresar precio">
                                        <small class="form-text text-muted">Monto en COP</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables,  $bundle->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha inicio</label>
                                    <div class="input-group">
                                        <input type="date" id="start_date" name="start_date" class="form-control daterange" value="{{ $bundle->start_date }}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha final</label>
                                    <div class="input-group">
                                        <input type="date" id="expire_at" name="expire_at" class="form-control daterange" value="{{ $bundle->expire_at }}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Cursos</label>
                                    <div class="input-group">
                                        {!! Form::select('courses[]', $courses, null, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'courses', 'data-placeholder' => 'Seleccionar cursos...']) !!}
                                    </div>
                                    <label id="courses-error" class="error d-none" for="courses"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Descripción</label>

                                    <div class="quill-wrapper">
                                        <div  id="descriptions">{!! clean($bundle->description, 'content') !!}</div>
                                    </div>
                                    <label id="description-error" class="error d-none" for="description"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                    </button>
                                    <a href="{{ route('manager.bundles') }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
                                        Cancelar
                                    </a>
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

            $('#meta_keywords').tagsinput({
                maxTags: 15
            });

            $('#courses').select2({ placeholder: 'Seleccionar cursos...', allowClear: true });
            var selectedCourses = @json($selectedCourses);
            if (selectedCourses.length > 0) {
                $('#courses').val(selectedCourses).trigger('change');
            }

            $("#formBundles").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    price: {
                        required: true,
                        number: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    description: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    available: {
                        required: true,
                    },
                    'courses[]': {
                        required: true,
                    },
                    meta_title: {
                        required: false,
                        minlength: 3,
                        maxlength: 100,
                    },
                    meta_description: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    'meta_keywords[]': {
                        required: false,
                    },
                    start_date: {
                        required: true,
                    },
                    expire_at: {
                        required: true,
                    },
                    thumbnail: {
                        required: function() {
                            return $("#status").val() == "false" ? true : false;
                        }
                    }
                },
                messages: {
                    meta_title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    price: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    meta_keywords: {
                        required: "Es necesario un opción.",
                    },
                    courses: {
                        required: "Es necesario un opción.",
                    },
                    available: {
                        required: "Es necesario un opción.",
                    },
                    start_date: {
                        required: "Es necesario un opción.",
                    },
                    expire_at: {
                        required: "Es necesario un opción.",
                    },
                    description: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    meta_description: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
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

                    var $form = $('#formBundles');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var title = $("#title").val();
                    var price = $("#price").val();
                    var meta_title = $("#meta_title").val();
                    var meta_keywords = $("#meta_keywords").val();
                    var meta_description = $("#meta_description").val();
                    var description = $("#description").val();
                    var expire_at = $("#expire_at").val();
                    var start_date = $("#start_date").val();
                    var available = $("#available").val();
                    var courses = $("#courses").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('price', price);
                    formData.append('meta_title', meta_title);
                    formData.append('meta_keywords', meta_keywords);
                    formData.append('meta_description', meta_description);
                    formData.append('description', description);
                    formData.append('expire_at', expire_at);
                    formData.append('start_date', start_date);
                    formData.append('available', available);
                    formData.append('courses', Array.isArray(courses) ? courses.join(',') : (courses || ''));

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.bundles.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){
                            
                                myThumbnail.processQueue();

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                myThumbnail.on("queuecomplete", function() {
                                   
                                });

                                setTimeout(function() {
                                     window.location = "{{ route('manager.bundles') }}";
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
                    url: "{{ route('manager.bundles.thumbnails') }}",
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

                        var myThumbnail = this;

                        item = $("#slack").val();

                        $.getJSON("{{ route('manager.bundles.thumbnails.get', ':item') }}".replace(':item', item), function(data) {

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
                            let bundle = document.getElementById('slack').value;
                            formData.append('bundle', bundle);
                        });

                        myThumbnail.on("addedfile", function(file) {
                            $("#thumbnail").val(file.name);
                            $("#formBundles").validate().element("#thumbnail");
                        });

                        myThumbnail.on("removedfile", function(file) {
                            $("#thumbnail").val('');
                            $("#formBundles").validate().element("#thumbnail");
                            if (file.id) {
                                $.ajax({
                                    type: 'DELETE',
                                    url: "{{ route('manager.bundles.thumbnails.delete', ':id') }}".replace(':id', file.id),
                                    success: function(result) {
                                        $("#status").val('false');
                                    }
                                });
                            }

                        });

                        myThumbnail.on('resetFiles', function() {
                            $("#status").val('false');
                            myThumbnail.removeAllFiles();
                        });

                        myThumbnail.on("success", function(file, response) {
                            $("#status").val('true');
                        });

                        myThumbnail.on("queuecomplete", function() {
                            $("#status").val('true');
                        });

                        myThumbnail.on("complete", function() {
                            $("#status").val('true');
                        });
                    }
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
                toolbar: toolbarOptions
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
            $('#description').text(description.container.firstChild.innerHTML);
        });

        var meta_description = new Quill('#meta_descriptions', {
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        meta_description.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        meta_description.on('text-change', function(delta, oldDelta, source) {
            $('#meta_description').text(meta_description.container.firstChild.innerHTML);
        });


    </script>

@endpush





