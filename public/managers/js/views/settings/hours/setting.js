$(document).on('submit', '#formHours', function (e) {
    e.preventDefault();
});

function bussinesshourSubmit() {
    var startStatus = 0;
    var endStatus = 0;
    var openEle = 0;

    document.querySelectorAll('.sprukoopen').forEach(function (ele) {
        if (!ele.value) {
            return;
        }

        if (ele.value === 'Abierto') {
            openEle += 1;
        }

        if (ele.closest('td').nextElementSibling.querySelector('.sprukostarttime').value) {
            startStatus += 1;
            if (ele.closest('td').nextElementSibling.querySelector('.sprukostarttime').value === '24H') {
                endStatus += 1;
            }
        }

        if (ele.closest('td').nextElementSibling.nextElementSibling.querySelector('.sprukoendtime').value) {
            endStatus += 1;
        }
    });

    var subBtn = document.querySelector('#bussinesshourSubmit');
    subBtn.disabled = !(openEle == startStatus && openEle == endStatus);
}
bussinesshourSubmit();

var dayListEle = document.querySelectorAll('.sprukoweeks');
var dayListArr = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];

function reorder(data, index) {
    return data.slice(index).concat(data.slice(0, index));
}

function daySort() {
    var startDay = dayListEle[0].value;
    if (startDay) {
        for (var i = 0; i <= dayListArr.length; i++) {
            if (dayListArr[i] === startDay) {
                var newDayList = reorder(dayListArr, i);
                dayListEle.forEach(function (element, ind) {
                    if (ind >= 1) {
                        element.value = newDayList[ind];
                    }
                });
            }
        }
        $('.sprukoopen').val('Abierto').trigger('change.select2');
        $('.sprukostarttime').val('0').trigger('change.select2');
        $('.sprukostarttime').prop('disabled', false);
        $('.sprukoendtime').val('0').trigger('change.select2');
        $('.sprukoendtime').prop('disabled', false);
    }
    bussinesshourSubmit();
}
$('.sprukoweeks').on('change', daySort);

$('.sprukostarttime').on('change', function (e) {
    var value = e.target.value;
    var tdfind = $(this).closest('tr').find('.tr_clone');
    var selectEle = tdfind[0].firstElementChild;

    if (value == '24H') {
        $(this).closest('tr').find('.tr_clone select').val('').trigger('change');
        selectEle.disabled = true;
    } else {
        selectEle.disabled = false;
    }

    bussinesshourSubmit();
});

$('.sprukoendtime').on('change', function () {
    bussinesshourSubmit();
});

$('.sprukoopen').on('change', function (e) {
    var sprukovalue = e.target.value;
    var tdfind1 = $(this).closest('tr').find('.tr_clone1');
    var tdfind2 = $(this).closest('tr').find('.tr_clone');
    var selectEle1 = tdfind1[0].firstElementChild;
    var selectEle2 = tdfind2[0].firstElementChild;

    if (sprukovalue == 'Cerrado') {
        $(this).closest('tr').find('.tr_clone select').val('').trigger('change');
        $(this).closest('tr').find('.tr_clone1 select').val('').trigger('change');
        selectEle1.disabled = true;
        selectEle2.disabled = true;
        selectEle2.value = null;
    } else {
        selectEle1.disabled = false;
        selectEle2.disabled = false;
    }

    bussinesshourSubmit();
});

$(window).on('load', function () {
    var startDay = dayListEle[0].value;
    if (startDay) {
        for (var i = 0; i <= dayListArr.length; i++) {
            if (dayListArr[i] === startDay) {
                var newDayList = reorder(dayListArr, i);
                dayListEle.forEach(function (element, ind) {
                    if (ind >= 1) {
                        element.value = newDayList[ind];
                    }
                });
            }
        }
    }

    $.map($('.sprukostarttime'), function (val) {
        var value = $(val).val();
        var tdfind = $(val).closest('tr').find('.tr_clone');
        var selectEle = tdfind[0].firstElementChild;
        if (value == '24H') {
            selectEle.disabled = true;
        }
    });

    $.map($('.sprukoopen'), function (value) {
        var val = $(value).val();
        var tdfind1 = $(value).closest('tr').find('.tr_clone1');
        var tdfind2 = $(value).closest('tr').find('.tr_clone');
        var selectEle1 = tdfind1[0].firstElementChild;
        var selectEle2 = tdfind2[0].firstElementChild;

        if (val == 'Cerrado') {
            selectEle1.disabled = true;
            selectEle2.disabled = true;
        }
    });

    $('body').on('click', '#bussinesshourRest', function () {
        $('.sprukoweeks').html('');
        dayListEle.forEach(function (e) { e.value = ''; });
        $('.sprukoopen').html('');
        $('.sprukostarttime').html('');
        $('.sprukoendtime').html('');
    });
});

$(document).ready(function () {
    var urls = $('#formHours').data('urls');

    $('#formHours').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            hourstitle: {
                required: true,
                minlength: 1,
                maxlength: 200,
            },
            hourssubtitle: {
                required: true,
                minlength: 1,
                maxlength: 200,
            },
        },
        messages: {
            hourstitle: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 200 caracter',
            },
            hourssubtitle: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 200 caracter',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formHours');
            var formData = new FormData($form[0]);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: urls.update,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location.href = urls.dashboard;
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        $('.errors').text(response.message);
                        $('.errors').removeClass('d-none');
                    }
                }
            });
        }
    });
});
