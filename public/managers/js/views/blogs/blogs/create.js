Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#blogs-create').data('config') || {};

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
                required: false,
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
        errorPlacement: function (error, element) {
            if (element.attr("id") == "thumbnail") {
                error.insertAfter("#thumbnail");
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {

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
                url: config.routes.store,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        slack = response.slack;
                        $("#slack").val(slack);
                        myThumbnail.processQueue();

                        message = response.message;

                        toastr.success(message, "Operación exitosa", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        myThumbnail.on("queuecomplete", function () {
                            setTimeout(function () {
                                window.location = config.routes.index;
                            }, 2000);
                        });

                    } else {

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
        url: config.routes.thumbnails,
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
        init: function () {

            var myDropzone = this;

            item = $("#slack").val();

            myDropzone.on("maxfilesexceeded", function (file) {
                this.removeFile(file);
            });

            myDropzone.on('sending', function (file, xhr, formData) {
                let blog = document.getElementById('slack').value;
                formData.append('blog', blog);
            });

            myDropzone.on("addedfile", function (file) {
                $("#thumbnail").val(file.name);
                $("#formBlogs").validate().element("#thumbnail");
            });

            myDropzone.on("removedfile", function (file) {
                $("#thumbnail").val('');
                $("#formBlogs").validate().element("#thumbnail");
                if (file.id) {
                    $.ajax({
                        type: 'GET',
                        url: config.routes.thumbnailDelete.replace(':id', file.id),
                        success: function (result) {
                            $("#status").val('false');
                        }
                    });
                }
            });

            myDropzone.on('resetFiles', function () {
                $("#status").val('false');
                myDropzone.removeAllFiles();
            });


            myDropzone.on("success", function (file, response) {
                $("#status").val('true');
            });

            myDropzone.on("queuecomplete", function () {

            });

        }
    });


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
    ['link', 'image', 'video'],
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

description.on('text-change', function (delta, oldDelta, source) {

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

content.on('text-change', function (delta, oldDelta, source) {
    $('#content').val(content.container.firstChild.innerHTML);
});
