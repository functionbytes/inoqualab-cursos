$(document).ready(function () {
    $('#search').on('keypress', function (e) {
        if (e.which === 13) {
            $('#formFilter').submit();
        }
    });

    $('#searchButton').on('click', function () {
        $('#formFilter').submit();
    });
});
