@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formTopic" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <textarea style="display: none"  id="description" name="description"></textarea>
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="course" name="course" value="{{$course->slack}}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear quiz</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Clase</label>
                                    <div class="input-group">
                                        {!! Form::select('lesson', $lessons, null , ['class' => 'select2 form-control','id' => 'lesson']) !!}
                                    </div>
                                    <label id="lesson-error" class="error d-none" for="lesson"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Modalidad</label>
                                    <div class="input-group">
                                        {!! Form::select('type', $types, null , ['class' => 'select2 form-control','id' => 'type']) !!}
                                    </div>
                                    <label id="type-error" class="error d-none" for="type"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Dias minimo</label>
                                        <input type="text" class="form-control" id="duration"  name="duration" value="" placeholder="Ingresar cantidad de duración">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Dias maximos</label>
                                        <input type="text" class="form-control" id="day"  name="day" value="" placeholder="Ingresar cantidad dias disponible">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Tiempo</label>
                                        <input type="text" class="form-control" id="timer"  name="timer" value="" placeholder="Ingresar cantidad de tiempo del examen">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Cantidad preguntas</label>
                                        <input type="text" class="form-control" id="question"  name="question" value="" placeholder="Ingresar cantidad preguntas">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Preguntas correctas</label>
                                        <input type="text" class="form-control" id="mark"  name="mark" value="" placeholder="Ingresar cantidad de preguntas correctas">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Detalle</label>
                                    <div class="quill-wrapper">
                                        <div  id="descriptions"></div>
                                    </div>
                                    <label id="description-error" class="error d-none" for="description"></label>
                                </div>
                            </div>
                            <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                    Guardar
                                </button>
                                <a href="{{ route('manager.courses.quiz', $course->slack) }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
                                    Cancelar
                                </a>
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


            $("#formTopic").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 200,
                    },
                    timer: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999,
                    },
                    question: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999,
                    },
                    duration: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999,
                    },
                    day: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999,
                    },
                    mark: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999,
                    },
                    class: {
                        required: true,
                    },
                    type: {
                        required: true,
                    },
                    available: {
                        required: true,
                    },
                    description: {
                        required: false,
                    },

                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 200 caracter",
                    },
                    timer: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe contener ser mayor 0",
                        max: "Debe contener ser menor a 999",
                    },
                    mark: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe contener ser mayor 0",
                        max: "Debe contener ser menor a 999",
                    },
                    question: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe contener ser mayor 0",
                        max: "Debe contener ser menor a 999",
                    },
                    duration: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe contener ser mayor 0",
                        max: "Debe contener ser menor a 999",
                    },
                    day: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe contener ser mayor 0",
                        max: "Debe contener ser menor a 999",
                    },
                    class: {
                        required: "Es necesario un opción.",
                    },
                    type: {
                        required: "Es necesario un opción.",
                    },
                    available: {
                        required: "Es necesario un opción.",
                    },
                    description: {
                        required: "El descripción es necesario.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formTopic');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var title = $("#title").val();
                    var timer = $("#timer").val();
                    var mark = $("#mark").val();
                    var question = $("#question").val();
                    var duration = $("#duration").val();
                    var day = $("#day").val();
                    var type = $("#type").val();
                    var lesson = $("#lesson").val();
                    var description = $("#description").val();
                    var available = $("#available").val();
                    var course = $("#course").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('timer', timer);
                    formData.append('mark', mark);
                    formData.append('question', question);
                    formData.append('duration', duration);
                    formData.append('day', day);
                    formData.append('type', type);
                    formData.append('lesson', lesson);
                    formData.append('description', description);
                    formData.append('available', available);
                    formData.append('course', course);

                    $.ajax({
                        url: "{{ route('manager.courses.quiz.store') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function() {
                            location.href = "{{route('manager.courses.quiz', $course->slack)}}";

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







