$(function () {
    var $modal = $('#resumeModal');
    if (!$modal.length) {
        return;
    }

    var modal = new bootstrap.Modal($modal[0], { backdrop: 'static', keyboard: false });
    modal.show();
});
