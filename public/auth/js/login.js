$(document).ready(function () {
    // Alternar visibilidad de la contraseña
    $('#btnToggle').on('click', function () {
        const passwordInput = $('#password');
        const eyeIcon = $('#eyeIcon');
        const isPassword = passwordInput.attr('type') === 'password';

        passwordInput.attr('type', isPassword ? 'text' : 'password');

        // Cambiar el icono entre ojo abierto y cerrado
        if (isPassword) {
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash'); // Ojo cerrado
        } else {
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye'); // Ojo abierto
        }
    });
});
