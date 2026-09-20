$(function () {
    function pintar($row) {
        $('#cdName').text($row.data('title'));
        $('#cdIssued').text($row.data('issued'));
        $('#cdCode').text($row.data('code'));
        $('#cdCode2').text($row.data('code'));
        $('#cdDownload').attr('href', $row.data('download'));
        $('#cdView').attr('href', $row.data('view'));
    }

    $('.cd-row').on('click', function () {
        var $row = $(this);
        $('.cd-row').removeClass('is-active');
        $row.addClass('is-active');
        pintar($row);
    });

    // Estado inicial: la primera credencial de la lista.
    var $primera = $('.cd-row').first();
    if ($primera.length) { pintar($primera); }
});
