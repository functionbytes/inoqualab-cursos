@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInstructions" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $instruction->slack }}">
                    <textarea style="display: none"  id="short" name="short">{!! $instruction->short !!}</textarea>
                    <textarea style="display: none"  id="description" name="description">{!! $instruction->description !!}</textarea>
                    
                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar instrucciones</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value="{{ $instruction->title  }}" >
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $instruction->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categorias</label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, $instruction->category_id  , ['class' => 'select2 form-control' ,'name' => 'categorie', 'id' => 'categorie' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Etiquetas</label>
                                    <input type="text" class="form-control" id="tags" name="tags" value="{{ $instruction->tags  }}" >
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="control-label col-form-label">Descripcion corta</label>
                                <div>
                                    <div id="shorts">{!! $instruction->short !!}</div>
                                </div>
                                <label id="short-error" class="error d-none" for="short"></label>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="control-label col-form-label">Descripción</label>
                                <div>
                                    <div id="descriptions">{!! $instruction->description !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
                            </div>

                            <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                </button>
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

       
        $('#tags').tagsinput({
                maxTags: 15
        });

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
            $('#description').val(text);
        });


        var short = new Quill('#shorts', {
            modules: {
                toolbar: toolbarOptions,
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
            });


            short.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
            });

            short.on('text-change', function(delta, oldDelta, source) {
            var text = short.container.firstChild.innerHTML.replaceAll("<p><br></p>", "");
            $('#short').val(text);
            });


        $(document).ready(function() {


            $("#formInstructions").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                    },
                    short: {
                        required: true,
                        minlength: 3,
                        maxlength: 500,
                    },
                    description: {
                        required: true,
                        minlength: 3,
                    },
                    available: {
                        required: true,
                    },
                    categorie: {
                        required: true,
                    },
                    'tags[]': {
                        required: false,
                    },
                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                    },
                    short: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al maximo 500 caracter",
                    },
                    description: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                    },
                    available: {
                        required: "Es necesario un estado.",
                    },
                    categorie: {
                        required: "Es necesario un estado.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formInstructions');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var short = $("#short").val();
                    var description = $("#description").val();
                    var title = $("#title").val();
                    var available = $("#available").val();
                    var categorie = $("#categorie").val();
                    var tags = $("#tags").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('description', description);
                    formData.append('short', short);
                    formData.append('available', available);
                    formData.append('categorie', categorie);
                    formData.append('tags', tags);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);
                    

                    $.ajax({
                        url: "{{ route('manager.instructions.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                setTimeout(function() {
                                    window.location = "{{ route('manager.instructions') }}";
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

        });

    </script>


@endpush



