@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCourses" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <textarea style="display: none"  id="who" name="who"></textarea>
                    <textarea style="display: none"  id="learn" name="learn"></textarea>
                    <textarea style="display: none"  id="short" name="short"></textarea>
                    <textarea style="display: none"  id="requirement" name="requirement"></textarea>
                    <textarea style="display: none"  id="detail" name="detail"></textarea>
                    <input type="hidden" id="status" name="status" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube la imagen de portada del curso. Se mostrará en el catálogo y en la página del curso, así que se recomienda una imagen clara y de buena calidad.
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
                            <h5 class="mb-0">Crear curso</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos del curso. Los campos marcados como obligatorios deben diligenciarse para poder publicarlo.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="form-section-title"><i class="fas fa-circle-info"></i> Información general</div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title"  name="title" value=""  placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Video</label>
                                        <input type="text" class="form-control" id="film"  name="film" value=""  placeholder="Ej: https://www.youtube.com/watch?v=...">
                                        <small class="form-text text-muted">Enlace de YouTube o Vimeo para el video de vista previa del curso (opcional)</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Duración <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="duration"  name="duration" value=""  placeholder="Ingresar duración">
                                        <small class="form-text text-muted">Horas totales del curso</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Dias <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="day"  name="day" value=""  placeholder="Ingresar dias">
                                        <small class="form-text text-muted">Días de acceso al contenido</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title"><i class="fas fa-tag"></i> Precio y promoción</div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Precio <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="price"  name="price" value=""  placeholder="Ej: 75000">
                                        <small class="form-text text-muted">Precio en COP</small>
                                </div>
                            </div>

                            <div class="col-6 d-none divDiscount">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Descuento</label>
                                        <input type="text" class="form-control" id="discount"  name="discount" value=""  placeholder="Ingresar descuento">
                                        <small class="form-text text-muted">Porcentaje de descuento (1-100)</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title"><i class="fas fa-certificate"></i> Certificación y categoría</div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Certificador <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certification', $certifications, null , ['class' => 'select2 form-control','id' => 'certification']) !!}
                                    </div>
                                    <label id="certification-error" class="error d-none" for="certification"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Entidad certificadora <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certifier', $certifiers, null , ['class' => 'select2 form-control','id' => 'certifier']) !!}
                                    </div>
                                    <label id="certifier-error" class="error d-none" for="certifier"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categoria <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, null , ['class' => 'select2 form-control','id' => 'categorie']) !!}
                                    </div>
                                    <label id="categorie-error" class="error d-none" for="categorie"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Certificación <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certificate', $conditions, null  , ['class' => 'select2 form-control','id' => 'certificate']) !!}
                                    </div>
                                    <label id="certificate-error" class="error d-none" for="certificate"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-section-title"><i class="fas fa-sliders"></i> Visibilidad y configuración</div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Destacado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('featured', $conditions, null , ['class' => 'select2 form-control','id' => 'featured']) !!}
                                    </div>
                                    <label id="featured-error" class="error d-none" for="featured"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Nivel</label>
                                    <div class="input-group">
                                        <select class="select2 form-control" id="level" name="level">
                                            <option value="">Sin definir</option>
                                            <option value="Principiante">Principiante</option>
                                            <option value="Intermedio">Intermedio</option>
                                            <option value="Avanzado">Avanzado</option>
                                        </select>
                                    </div>
                                    <label id="level-error" class="error d-none" for="level"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Calificación (0 a 5)</label>
                                    <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" step="0.1" value="0" placeholder="Ej: 4.5">
                                    <label id="rating-error" class="error d-none" for="rating"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Website <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('website', $conditions, null, ['class' => 'select2 form-control','id' => 'website']) !!}
                                    </div>
                                    <label id="website-error" class="error d-none" for="website"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Pago <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('payment', $conditions, null , ['class' => 'select2 form-control','id' => 'payment']) !!}
                                    </div>
                                    <label id="payment-error" class="error d-none" for="payment"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Examen <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('exam', $conditions, null , ['class' => 'select2 form-control','id' => 'exam']) !!}
                                    </div>
                                    <label id="exam-error" class="error d-none" for="exam"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Promocion <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('promotion', $conditions,null, ['class' => 'select2 form-control','id' => 'promotion']) !!}
                                    </div>
                                    <label id="promotion-error" class="error d-none" for="promotion"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>


                    <div class="col-12">
                        <div class="form-section-title"><i class="fas fa-align-left"></i> Contenido descriptivo</div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Descripcion</label>
                            <div class="quill-wrapper">
                                <div  id="shorts"></div>
                            </div>
                            <label id="short-error" class="error d-none" for="short"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Detalle</label>
                            <div class="quill-wrapper">
                                <div  id="details"></div>
                            </div>
                            <label id="detail-error" class="error d-none" for="detail"></label>
                        </div>
                    </div>

                    <div class="col-12">
                            <div class="mb-3">
                                <label class="col-form-label">Lo que aprenderas</label>
                                <div class="quill-wrapper">
                                    <div  id="learns"></div>
                                </div>
                                <label id="learn-error" class="error d-none" for="learn"></label>
                            </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Para quien es el curso</label>
                            <div class="quill-wrapper">
                                <div  id="whos"></div>
                            </div>
                            <label id="who-error" class="error d-none" for="who"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Requerimientos</label>
                            <div class="quill-wrapper">
                                <div  id="requirements"></div>
                            </div>
                            <label id="requirement-error" class="error d-none" for="requirement"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
                            <a href="{{ route('manager.courses') }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
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

            $("#formCourses").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    film: {
                        required: false,
                        minlength: 3,
                        maxlength: 100,
                    },
                    price: {
                        required: true,
                        number: true,
                        min: 1,
                        max: 1000000,
                    },
                    discount: {
                        required: function (element) {

                            var item = $("#promotion").val();

                            if (item == 1) {
                                return true;
                            }else{
                                return false;
                            }

                        },
                        number: true,
                        min: 1,
                        max: 1000000,
                    },

                    short: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    detail: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    who: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    learn: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    requirement: {
                        required: false,
                        minlength: 0,
                        maxlength: 2000,
                    },
                    day: {
                        required: true,
                        number: true,
                        min: 1,
                        max: 365,
                    },
                    duration: {
                        required: true,
                        number: true,
                        min: 1,
                        max: 10000,
                    },
                    certification: {
                        required: true,
                    },
                    certifier: {
                        required: true,
                    },
                    website: {
                        required: true,
                    },
                    categorie: {
                        required: true,
                    },
                    certificate: {
                        required: true,
                    },
                    featured: {
                        required: true,
                    },
                    available: {
                        required: true,
                    },
                    payment: {
                        required: true,
                    },
                    exam: {
                        required: true,
                    },
                    promotion: {
                        required: true,
                    },
                    thumbnail: {
                        required: function() {
                            return $("#status").val() == "false" ? true : false;
                        }
                    }


                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    film: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    price: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe ser minimo de 1 ",
                        max: "Debe ser maximor 1000000",
                    },
                    duration: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe ser minimo de 1 ",
                        max: "Debe ser maximor 10000",
                    },
                    day: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe ser minimo de 1 ",
                        max: "Debe ser maximor 365",
                    },
                    discount: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe ser minimo de 1 ",
                        max: "Debe ser maximor 1000000",
                    },
                    certifier: {
                        required: "Es necesario un opción.",
                    },
                    certification: {
                        required: "Es necesario un opción.",
                    },
                    categorie: {
                        required: "Es necesario un opción.",
                    },
                    certificate: {
                        required: "Es necesario un opción.",
                    },
                    website: {
                        required: "Es necesario un opción.",
                    },
                    featured: {
                        required: "Es necesario un opción.",
                    },
                    available: {
                        required: "Es necesario un opción.",
                    },
                    payment: {
                        required: "Es necesario un opción.",
                    },
                    exam: {
                        required: "Es necesario un opción.",
                    },
                    promotion: {
                        required: "Es necesario un opción.",
                    },
                    short: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    detail: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    who: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    learn: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 2000 caracter",
                    },
                    requirement: {
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

                    var $form = $('#formCourses');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var title = $("#title").val();
                    var price = $("#price").val();
                    var discount = $("#discount").val();
                    var short = $("#short").val();
                    var detail = $("#detail").val();
                    var who = $("#who").val();
                    var learn = $("#learn").val();
                    var categorie = $("#categorie").val();
                    var website = $("#website").val();
                    var requirement = $("#requirement").val();
                    var film = $("#film").val();
                    var duration = $("#duration").val();
                    var day = $("#day").val();
                    var certifier = $("#certifier").val();
                    var certification = $("#certification").val();
                    var available = $("#available").val();
                    var featured = $("#featured").val();
                    var payment = $("#payment").val();
                    var exam = $("#exam").val();
                    var promotion = $("#promotion").val();

                    formData.append('slack', slack);
                    formData.append('title', title);
                    formData.append('price', price);
                    formData.append('discount', discount);
                    formData.append('short', short);
                    formData.append('detail', detail);
                    formData.append('who', who);
                    formData.append('learn', learn);
                    formData.append('requirement', requirement);
                    formData.append('film', film);
                    formData.append('duration', duration);
                    formData.append('day', day);
                    formData.append('certifier', certifier);
                    formData.append('certification', certification);
                    formData.append('available', available);
                    formData.append('featured', featured);
                    formData.append('level', $('#level').val());
                    formData.append('rating', $('#rating').val());
                    formData.append('payment', payment);
                    formData.append('exam', exam);
                    formData.append('promotion', promotion);
                    formData.append('categorie', categorie);
                    formData.append('website', website);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.courses.store') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){
                            
                                $("#slack").val(response.slack);
                                myThumbnail.processQueue();

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                myThumbnail.on("queuecomplete", function() {
                                    setTimeout(function() {
                                        window.location = "{{ route('manager.courses') }}";
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



        });

    </script>


    <script type="text/javascript">

        $("#promotion").change(function(){

            if($('#promotion').val() == 0){
                $(".divDiscount").addClass("d-none");
            }else{
                $(".divDiscount").removeClass("d-none");
            }

        });


        @include("managers.views.courses.courses._quill-editors")


        $.ajaxSetup({
           headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
        });


        var myThumbnail = new Dropzone("div#thumbnail", {
            paramName: "file",
            url: "{{ route('manager.courses.thumbnails') }}",
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


                myThumbnail.on("maxfilesexceeded", function(file) {
                    this.removeFile(file);
                });

                myThumbnail.on('sending', function(file, xhr, formData) {
                    let course = document.getElementById('slack').value;
                    formData.append('course', course.replace('"',''));

                });

                myThumbnail.on("addedfile", function(file) {
                    $("#thumbnail").val(file.name);
                    $("#formCourses").validate().element("#thumbnail");
                });

                myThumbnail.on("removedfile", function(file) {
                    $("#thumbnail").val('');
                    $("#formCourses").validate().element("#thumbnail");

                    if (file.id) {
                        $.ajax({
                        type: 'GET',
                        url: "{{ route('manager.courses.thumbnails.delete', ':id') }}".replace(':id', file.id),
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

    </script>

@endpush





