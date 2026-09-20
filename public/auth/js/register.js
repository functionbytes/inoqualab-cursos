$(document).ready(function() {
    $("#terms").on("change", function() {
        var checked = $(this).is(":checked");

        if (checked) {
            $('#addRegister').removeClass("register-disabled");
        } else {
            $('#addRegister').addClass("register-disabled");
        }
    });
});
