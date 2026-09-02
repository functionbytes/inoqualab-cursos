@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formTestimonies" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <textarea class="d-none" id="description" name="description">{!! clean($testimonie->description, 'content') !!}</textarea>
                    <input type="hidden" id="id" name="id" value="{{ $testimonie->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $testimonie->slack }}">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar testimonios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos del testimonio. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $testimonie->firstname }}" placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $testimonie->lastname }}" placeholder="Ingresar apellido">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Rol</label>
                                        <input type="text" class="form-control" id="role"  name="role" value="{{ $testimonie->role }}" placeholder="Ej: Estudiante certificado">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Ícono (Font Awesome)</label>
                                        <input type="text" class="form-control" id="icon"  name="icon" value="{{ $testimonie->icon }}" placeholder="Ej: fas fa-user-graduate">
                                        <label id="icon-error" class="error d-none" for="icon"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Calificación</label>
                                    <div class="input-group">
                                        {!! Form::select('rating', [1 => '1 estrella', 2 => '2 estrellas', 3 => '3 estrellas', 4 => '4 estrellas', 5 => '5 estrellas'], $testimonie->rating, ['class' => 'select2 form-control','id' => 'rating']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Número del contador</label>
                                        <input type="text" class="form-control" id="counter_value"  name="counter_value" value="{{ $testimonie->counter_value }}" placeholder="Ej: 50">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Sufijo del contador</label>
                                        <input type="text" class="form-control" id="counter_suffix"  name="counter_suffix" value="{{ $testimonie->counter_suffix }}" placeholder="Ej: K+, %, +">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Descripción del contador</label>
                                        <input type="text" class="form-control" id="benefit"  name="benefit" value="{{ $testimonie->benefit }}" placeholder="Ej: Estudiantes certificados con nuestros cursos">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Orden</label>
                                        <input type="number" class="form-control" id="position"  name="position" value="{{ $testimonie->position }}" min="0" placeholder="0">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $testimonie->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="col-form-label">Testimonio</label>
                                <div class="quill-wrapper">
                                    <div  id="descriptions">{!! clean($testimonie->description, 'content') !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
                             </div>
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
        Dropzone.autoDiscover = false;

        $(document).ready(function() {

            $("#formTestimonies").validate({
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
                    available: {
                        required: true,
                    },
                    description: {
                        required: true,
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
                    available: {
                        required: "Es necesario un estado.",
                    },
                    description: {
                        required: "La descripción es necesario.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formTestimonies');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var firstname = $("#firstname").val();
                    var lastname = $("#lastname").val();
                    var role = $("#role").val();
                    var icon = $("#icon").val();
                    var rating = $("#rating").val();
                    var benefit = $("#benefit").val();
                    var position = $("#position").val();
                    var counterValue = $("#counter_value").val();
                    var counterSuffix = $("#counter_suffix").val();
                    var description = $("#description").val();
                    var available = $("#available").val();

                    formData.append('slack', slack);
                    formData.append('firstname', firstname);
                    formData.append('lastname', lastname);
                    formData.append('role', role);
                    formData.append('icon', icon);
                    formData.append('rating', rating);
                    formData.append('benefit', benefit);
                    formData.append('counter_value', counterValue);
                    formData.append('counter_suffix', counterSuffix);
                    formData.append('position', position);
                    formData.append('description', description);
                    formData.append('available', available);


                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);


                    $.ajax({
                        url: "{{ route('manager.testimonies.update') }}",
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
                                        window.location = "{{ route('manager.testimonies') }}";
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

    </script>

@endpush



