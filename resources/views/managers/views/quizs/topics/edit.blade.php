@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formQuestions" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $question->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $question->slack }}"/>
                    <input type="hidden" id="class" name="class" value="{{ $topic->lesson_id }}"/>
                    <input type="hidden" id="topic" name="topic" value="{{ $topic->id }}"/>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar pregunta</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" value="{{ $topic->title }}" placeholder="Ingresar titulo" disabled>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Pregunta</label>
                                        <input type="text" class="form-control" id="question"  name="question" value="{{ $question->question }}" placeholder="Ingresar la pregunta">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $question->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Respuestas</label>
                                    <div class="input-group">
                                        @if ($question->type == '1')
                                            {!! Form::select('answer[]', $answers, Str::of($question->answer)->explode(','), ['class' => 'select2 f§orm-control','id' => 'answer', 'multiple' => 'multiple']) !!}
                                        @else
                                            {!! Form::select('answer', $answers, $question->answer , ['class' => 'select2 form-control','id' => 'answer']) !!}
                                        @endif
                                    </div>
                                    <label id="answer-error" class="error d-none" for="answer"></label>
                                </div>
                            </div>
                            @if ($question->type == 1)
                                <div class="col-6">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label  class="control-label col-form-label">A</label>
                                            <input type="text" class="form-control" id="a"  name="a" value="{{ $question->a }}" placeholder="Ingresar la respuesta a">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label  class="control-label col-form-label">B</label>
                                            <input type="text" class="form-control" id="b"  name="b" value="{{ $question->b }}" placeholder="Ingresar la respuesta b">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label  class="control-label col-form-label">C</label>
                                            <input type="text" class="form-control" id="c"  name="c" value="{{ $question->c }}" placeholder="Ingresar la respuesta c">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label  class="control-label col-form-label">D</label>
                                            <input type="text" class="form-control" id="d"  name="d" value="{{ $question->d }}" placeholder="Ingresar la respuesta d">
                                        </div>
                                    </div>
                                </div>

                            @endif
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


            $("#formQuestions").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    question: {
                        required: true,
                        minlength: 3,
                        maxlength: 400,
                    },
                    a: {
                        required: function (element) {
                            if ({{ $question->type }} == 1) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                        minlength: 3,
                        maxlength: 200,
                    },
                    b: {
                        required: function (element) {
                            if ({{ $question->type }} == 1) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                        minlength: 3,
                        maxlength: 200,
                    },
                    c: {
                        required: function (element) {
                            if ({{ $question->type }} == 1) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                        minlength: 3,
                        maxlength: 200,
                    },
                    d: {
                        required: function (element) {
                            if ({{ $question->type }} == 1) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                        minlength: 3,
                        maxlength: 200,
                    },
                    'answer[]': {
                        required: function (element) {
                            if ({{ $question->type }} == 1) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                    },
                    answer: {
                        required: function (element) {
                            if ({{ $question->type }} == 0) {
                                return true;
                            }else{
                                return false;
                            }
                        },
                    },
                    available: {
                        required: true,
                    },

                },
                messages: {
                    question: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 400 caracter",
                    },
                    a: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 400 caracter",
                    },
                    b: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 400 caracter",
                    },
                    c: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 400 caracter",
                    },
                    d: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 400 caracter",
                    },
                    answer: {
                        required: "Es necesario un opción.",
                    },
                    available: {
                        required: "Es necesario un opción.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formQuestions');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var question = $("#question").val();
                    var a = $("#a").val();
                    var b = $("#b").val();
                    var c = $("#c").val();
                    var d = $("#d").val();
                    var available = $("#available").val();
                    var answer = $("#answer").val();

                    formData.append('slack', slack);
                    formData.append('question', question);
                    formData.append('answer', answer);
                    formData.append('a', a);
                    formData.append('b', b);
                    formData.append('c', c);
                    formData.append('d', d);
                    formData.append('available', available);

                    $.ajax({
                        url: "{{ route('manager.courses.quiz.questions.update') }}",
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
                                    location.href = "{{route('manager.courses.quiz.questions', $topic->slack)}}";
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





