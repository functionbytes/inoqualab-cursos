Dropzone.autoDiscover = false;

$(document).ready(function () {

    var config = $('#courses-create').data('config') || {};

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
                    } else {
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
                required: false,
                number: true,
                min: 1,
                max: 365,
            },
            duration: {
                required: false,
                number: true,
                min: 1,
                max: 10000,
            },
            certification: {
                required: false,
            },
            certifier: {
                required: false,
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
                required: function () {
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
        errorPlacement: function (error, element) {
            if (element.attr("id") == "thumbnail") {
                error.insertAfter("#thumbnail");
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {

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

                        $("#slack").val(response.slack);
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

    $("#promotion").change(function () {

        if ($('#promotion').val() == 0) {
            $(".divDiscount").addClass("d-none");
        } else {
            $(".divDiscount").removeClass("d-none");
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

            var myThumbnail = this;

            item = $("#slack").val();


            myThumbnail.on("maxfilesexceeded", function (file) {
                this.removeFile(file);
            });

            myThumbnail.on('sending', function (file, xhr, formData) {
                let course = document.getElementById('slack').value;
                formData.append('course', course.replace('"', ''));

            });

            myThumbnail.on("addedfile", function (file) {
                $("#thumbnail").val(file.name);
                $("#formCourses").validate().element("#thumbnail");
            });

            myThumbnail.on("removedfile", function (file) {
                $("#thumbnail").val('');
                $("#formCourses").validate().element("#thumbnail");

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

            myThumbnail.on('resetFiles', function () {
                $("#status").val('false');
                myThumbnail.removeAllFiles();
            });

            myThumbnail.on("success", function (file, response) {
                $("#status").val('true');
            });

            myThumbnail.on("queuecomplete", function () {
                $("#status").val('true');
            });

            myThumbnail.on("complete", function () {
                $("#status").val('true');
            });

        }
    });

});
