@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCustomers" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $enterprise->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">
                    <input type="hidden" id="status" name="status" value="true">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Importar usuarios</h5>

                        </div>

                        <p class="card-subtitle mb-3 mt-3">
                            Sube el archivo con el listado de usuarios a importar para esta empresa.
                        </p>

                        <div class="dropzone dz-clickable dz-started" id="dropzoneThumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                    </div>

                     <div class="col-12"><div class="action-form border-top mt-4">
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

            $("#formCustomers").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    url: {
                        required: false,
                        url : true,
                        minlength: 0,
                        maxlength: 1000,
                    },
                    available: {
                        required: true,
                    },

                },
                messages: {
                    title: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    url: {
                        required: "El parametro es necesario.",
                        url: "Debe ingresar una url valida.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos c caracter",
                    },
                    available: {
                        required: "Es necesario un estado.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formCustomers');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();

                    formData.append('slack', slack);

                    $.ajax({
                        url: "/manager/enterprises/users/importation",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function() {

                            myThumbnail.processQueue();

                            var statuThumbnail = $("#status").val();
                            var statuEdit = $("#edit").val();

                            if (statuThumbnail == 'true' && statuEdit == 'true') {

                                window.location.href = "{{ route('manager.enterprises.users', $enterprise->slack) }}";
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

            var myThumbnail = new Dropzone("div#dropzoneThumbnail", {
                paramName: "file",
                url: "{{ route('manager.trusteds.thumbnails') }}",
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: false,
                acceptedFiles: ".png",
                parallelUploads: 1,
                maxFiles: 1,
                params: {
                    _token: token
                },
                init: function() {

                    var myThumbnail = this;

                    item = $("#slack").val();

                    $.getJSON('/manager/trusteds/get/thumbnails/' + item, function(data) {

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

                        let trusted = document.getElementById('slack').value;
                        formData.append('trusted', trusted.replace('"',''));

                    });

                    myThumbnail.on("addedfile", function(file) {

                        $("#status").val('false');

                    });

                    myThumbnail.on("removedfile", function(file) {

                        $("#status").val('false');

                        id = file.id;

                        $.ajax({
                            type: 'GET'
                            , url: '/manager/trusteds/delete/thumbnails/' + id
                            , success: function(result) {
                                $("#status").val('false');
                            }
                        });

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
                        uploadThumbnail();
                    });
                }
            });



        });

    </script>




@endpush



