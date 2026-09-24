$(function () {
    // #resumeModal (retomar) y #startModal (comenzar) son mutuamente
    // excluyentes en el blade -- como mucho uno de los dos existe en el DOM.
    var $modal = $('#resumeModal, #startModal');
    if (!$modal.length) {
        return;
    }

    var modal = new bootstrap.Modal($modal[0], { backdrop: 'static', keyboard: false });
    modal.show();
});
