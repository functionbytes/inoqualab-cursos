$(document).ready(function () {
    var $form = $('#formReport');

    $form.on('submit', function (e) {
        e.preventDefault();

        var query = {
            method: $('#method').val(),
            condition: $('#condition').val(),
        };

        window.location = $form.data('generateUrl') + '?' + $.param(query);
    });
});
