$(function () {
    $('#storageDriver').on('change', function () {
        $('#s3StorageSettings').toggleClass('d-none', !['s3', 'spaces'].includes($(this).val()));
    });

    $('#maxFileSize').on('input', function () {
        var kb = parseInt($(this).val() || 0, 10);
        var mb = (kb / 1024).toFixed(2);
        $('#maxFileSizeHelp').text('Tamaño máximo permitido por archivo (~' + mb + ' MB)');
    });
});
