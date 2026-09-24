$(function () {

    // Delegado (no .on directo): el hero tiene su propio disparador ademas
    // del boton de la tarjeta "Gestion de conexion", ambos comparten esta
    // clase y envian el mismo #form-disconnect.
    $(document).on('click', '.js-btn-disconnect', function () {
        if (!confirm('¿Desconectar la cuenta de Google? Se eliminarán los tokens almacenados.')) return;
        $('#form-disconnect').submit();
    });

});
