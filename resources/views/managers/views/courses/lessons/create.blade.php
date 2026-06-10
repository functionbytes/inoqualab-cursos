@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formLessons" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <textarea style="display: none"  id="detail" name="detail"></textarea>
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="course" name="course" value="{{ $course->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear clase</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para permitirte  <mark><code>introducir</code></mark> nueva información de manera sencilla y estructurada. A continuación, se presentan varios campos que deberás completar con los datos requeridos.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Curso</label>
                                        <input type="text" class="form-control"  value="{{ $course->title }}" placeholder="Ingresar titulo" disabled>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Tema</label>
                                    <div class="input-group">
                                        {!! Form::select('chapter', $chapters, null , ['class' => 'select2 form-control','id' => 'chapter']) !!}
                                    </div>
                                    <label id="chapter-error" class="error d-none" for="chapter"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Tipo</label>
                                    <div class="input-group">
                                        {!! Form::select('type', $types, null , ['class' => 'select2 form-control','id' => 'type']) !!}
                                    </div>
                                    <label id="type-error" class="error d-none" for="type"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Posición</label>
                                        <input type="text" class="form-control" id="position"  name="position" value="{{ $position }}" placeholder="Ingresar posición">
                                </div>
                            </div>

                            <div class="col-6 d-none divAudio divFiles">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Archivo</label>
                                        <input class="form-control" type="file" id="file"  name="file"   />
                                </div>
                            </div>
                            <div class="col-6 d-none divAudiovisual">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Plataforma</label>
                                    <select class="select2 form-control" id="platform" name="platform">
                                        <option value="">Seleccionar</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="vimeo">Vimeo</option>
                                    </select>
                                    <label id="platform-error" class="error d-none" for="platform"></label>
                                </div>
                            </div>

                            <div class="col-6 d-none divAudiovisual">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Link</label>
                                    <input type="text" class="form-control" id="url" name="url" value="" placeholder="Pegar enlace aquí">
                                    <small id="url-platform" class="mt-1 d-block"></small>
                                </div>
                            </div>

                            <div class="col-6 d-none  divAudiovisual divAudio">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Duración</label>
                                        <input type="text" class="form-control" id="duration" value="" name="duration"  placeholder="Ingresar duración">
                                </div>
                            </div>
                            <div class="col-6 d-none divFiles">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Tamaño</label>
                                        <input type="text" class="form-control" id="size" value="" name="size"  placeholder="Ingresar tamaño">
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
                                        <div id="details"></div>
                                    </div>
                                    <label id="detail-error" class="error d-none" for="detail"></label>
                                </div>
                            </div>

                             <div class="col-12">
                                 <div class="action-form border-top mt-4">
                                     <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                         Guardar
                                     </button>
                                     <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
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


            function detectVideoPlatform() {
                var url      = $('#url').val();
                var platform = $('#platform').val();
                var $label   = $('#url-platform');
                if (!url) { $label.text('').attr('class', 'mt-1 d-block'); return; }
                var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(url);
                var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(url);
                if (platform === 'youtube') {
                    $label.text(yt ? '✔ Enlace de YouTube válido' : '⚠ El enlace no corresponde a YouTube')
                          .attr('class', 'mt-1 d-block fw-semibold ' + (yt ? 'text-success' : 'text-warning'));
                } else if (platform === 'vimeo') {
                    $label.text(vm ? '✔ Enlace de Vimeo válido' : '⚠ El enlace no corresponde a Vimeo')
                          .attr('class', 'mt-1 d-block fw-semibold ' + (vm ? 'text-success' : 'text-warning'));
                } else {
                    var ok = yt || vm;
                    $label.text(ok ? (yt ? '✔ YouTube detectado' : '✔ Vimeo detectado') : '⚠ Debe ser un enlace de YouTube o Vimeo')
                          .attr('class', 'mt-1 d-block fw-semibold ' + (ok ? 'text-success' : 'text-warning'));
                }
            }

            $('#url, #platform').on('input change', function() { detectVideoPlatform(); });

            function toggleVisibility(value) {
                $(".divFiles, .divAudiovisual, .divAudio").addClass("d-none");
                if (value == 1) $(".divAudiovisual").removeClass("d-none");
                else if (value == 2) $(".divAudio").removeClass("d-none");
                else if (value == 3 || value == 4 || value == 5) $(".divFiles").removeClass("d-none");
            }

            $("#type").change(function() {
                toggleVisibility($(this).val());
                detectVideoPlatform($("#url").val());
            });

            $.validator.addMethod('videoUrl', function(value, element) {
                if (!value) return true;
                var platform = $('#platform').val();
                var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(value);
                var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(value);
                if (platform === 'youtube') return yt;
                if (platform === 'vimeo') return vm;
                return yt || vm;
            }, function(params, element) {
                var p = $('#platform').val();
                if (p === 'youtube') return 'El enlace debe ser de YouTube';
                if (p === 'vimeo')   return 'El enlace debe ser de Vimeo';
                return 'Ingresa un enlace válido de YouTube o Vimeo';
            });

            $("#formLessons").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    position: {
                        required: true,
                        number  : true,
                        minlength: 1,
                        maxlength: 10,
                    },
                    chapter: {
                        required: true,
                    },
                    platform: {
                        required: function(element) {
                            return $("#type").val() == '1';
                        },
                    },
                    url: {
                        required: function(element) {
                            return $("#type").val() == '1';
                        },
                        videoUrl: function(element) {
                            return $("#type").val() == '1';
                        },
                    },
                    size: {
                        required: function(element) {
                            return ['3', '4', '5'].includes($("#type").val());
                        },
                    },
                    type: {
                        required: true,
                    },
                    file: {
                        required: function(element) {
                            return ['2', '3', '4', '5'].includes($("#type").val());
                        },
                    },
                    duration: {
                        required: function(element) {
                            return ['1', '2'].includes($("#type").val());
                        },
                    },
                    available: {
                        required: true,
                    },
                    detail: {
                        required: false,
                    },
                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    position: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    type: {
                        required: "Es necesario un tema.",
                    },
                    platform: {
                        required: "Selecciona la plataforma del video.",
                    },
                    url: {
                        required: "El parametro es necesario.",
                        videoUrl: "Ingresa un enlace válido de YouTube o Vimeo",
                    },
                    size: {
                        required: "Es necesario un tema.",
                    },
                    file: {
                        required: "Es necesario un tema.",
                    },
                    duration: {
                        required: "Es necesario un tema.",
                    },
                    chapter: {
                        required: "Es necesario un tema.",
                    },
                    available: {
                        required: "Es necesario un estado.",
                    },
                    detail: {
                        required: "El detalle es necesario.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formLessons');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var title = $("#title").val();
                    var file = $("#file").val();
                    var duration = $("#duration").val();
                    var position = $("#position").val();
                    var size = $("#size").val();
                    var url = $("#url").val();
                    var chapter = $("#chapter").val();
                    var type = $("#type").val();
                    var detail = $("#detail").val();
                    var available = $("#available").val();
                    var course = $("#course").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('position', position);
                    formData.append('detail', detail);
                    formData.append('available', available);
                    formData.append('course', course);
                    formData.append('duration', duration);
                    formData.append('file', file);
                    formData.append('size', size);
                    formData.append('url', url);
                    formData.append('platform', $("#platform").val());
                    formData.append('chapter', chapter);
                    formData.append('type', type);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.courses.lessons.store') }}",
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
                                    location.href = "{{route('manager.courses.lessons', $course->slack)}}";
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

        var detail = new Quill('#details', {

            modules: {
                toolbar: toolbarOptions,
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow'
        });

       
        detail.on('selection-change', function (range, oldRange, source) {
            if (range === null && oldRange !== null) {
                $('body').removeClass('overlay-disabled');
            } else if (range !== null && oldRange === null) {
                $('body').addClass('overlay-disabled');
            }
        });

        detail.on('text-change', function(delta, oldDelta, source) {

            var text = detail.container.firstChild.innerHTML.replaceAll("<p><br></p>", "");
            $('#detail').val(text);
        });

    </script>

@endpush





