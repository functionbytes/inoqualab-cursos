$(document).ready(function () {

    $('.actionPayment').click(function () {
        var payment = $('input:radio[name=payment]:checked').val();

        if (payment == 'wompi') {
            $('.waybox-button').click();
            return;
        }

        var cellphone = $(this).data('whatsapp');
        var win = window.open('https://api.whatsapp.com/send?phone=57' + cellphone, '_blank');
        win.focus();
    });

});
