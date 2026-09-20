var totalques = 0;

$(document).ready(function () {
    totalques = $('.jumbotron').length;

    var i = 1;
    var count = 0;

    $('#next').click(function () {
        var totalques = $('.jumbotron').length;
        var type = $('#type').val();
        var x = $('#next').val();
        var y = $('#prev').val();

        if (type == 0) {
            var numberNotChecked = $('#more_exam' + count).find('input[type="radio"]:checked').length;

            if (numberNotChecked > 0) {
                i++;
                x++;

                $('#prev').show();

                if (x < totalques) {
                    var z = x - 1;

                    $('#more_exam' + x).show('fast');
                    $('#more_exam' + z).hide('fast');
                    $('#next').val(x);
                    $('#prev').val(x);

                    if (i == totalques) {
                        $('#next').attr('type', 'submit');
                    }
                }

                if (x == totalques) {
                    $('#question-form').submit();
                }

                progres = (x / totalques) * 100;
                $('#progressbar').css('width', progres + '%').attr('aria-valuenow', Math.round(progres));

                count++;
            }
        }

        if (type == 1) {
            $('#prev').show();

            var numberNotChecked = $('#more_exam' + count).find('input:checkbox:not(":checked")').length;

            if (numberNotChecked != 4) {
                i++;
                x++;

                $('#prev').show();

                if (x < totalques) {
                    var z = x - 1;

                    $('#more_exam' + x).show('fast');
                    $('#more_exam' + z).hide('fast');
                    $('#next').val(x);
                    $('#prev').val(x);

                    if (i == totalques) {
                        $('#next').attr('type', 'submit');
                    }
                }

                if (x == totalques) $('#question-form').submit();
            }

            progres = (x / totalques) * 100;
            $('#progressbar').css('width', progres + '%').attr('aria-valuenow', Math.round(progres));

            count++;
        }
    });

    $('#prev').click(function () {
        i--;
        count--;

        var totalques = $('.jumbotron').length;
        var x = $('#next').val();
        var y = $('#prev').val();

        $('#next').removeAttr('type');
        $('#next').show();

        y--;

        if (y == 0) {
            $('#next').val(0);
            $('#prev').val(1);
            $('#prev').hide();
        } else {
            $('#next').val(y);
            $('#prev').val(y);
        }

        $('#more_exam' + y).show('fast');
        $('#more_exam' + x).hide();

        progres = (x / totalques) * 100;
        $('#progressbar').css('width', progres + '%').attr('aria-valuenow', Math.round(progres));
    });
});
