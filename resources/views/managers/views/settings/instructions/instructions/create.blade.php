@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInstructions" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}


                    <textarea class="d-none" id="short" name="short"></textarea>
                    <textarea class="d-none" id="description" name="description"></textarea>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear instrucciones</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos de la instruccion. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
                        </p>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categorias</label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, null , ['class' => 'select2 form-control' ,'name' => 'categorie', 'id' => 'categorie' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Etiquetas</label>
                                    <input type="text" class="form-control" id="tags" name="tags" value="" >
                                </div>
                            </div>


                            <div class="col-12">
                                <label class="control-label col-form-label">Descripcion corta</label>
                                <div class="">
                                    <div id="shorts"></div>
                                </div>
                                <label id="short-error" class="error d-none" for="short"></label>
                            </div>



                            <div class="col-12">
                                <label class="control-label col-form-label">Descripción</label>
                                <div class="">
                                    <div id="descriptions"></div>
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
                    description: {
                        required: true,
                        minlength: 3,
                    },
                    short: {
                        required: true,
                        minlength: 3,
                        maxlength: 500,
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
                    var title = $("#title").val();
                    var available = $("#available").val();
                    var description = $("#description").val();
                    var short = $("#short").val();
                    var categorie = $("#categorie").val();
                    var tags = $("#tags").val();

                    formData.append('title', title);
                    formData.append('available', available);
                    formData.append('description', description);
                    formData.append('short', short);
                    formData.append('categorie', categorie);
                    formData.append('tags', tags);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);


                    $.ajax({
                        url: "{{ route('manager.instructions.store') }}",
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


