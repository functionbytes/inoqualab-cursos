$(function () {

    var $page = $('#users-inscriptions-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

});
