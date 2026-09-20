$(function () {

    $('#btn-disconnect').on('click', function () {
        if (!confirm('¿Desconectar la cuenta de Google? Se eliminarán los tokens almacenados.')) return;
        $('#form-disconnect').submit();
    });

});
