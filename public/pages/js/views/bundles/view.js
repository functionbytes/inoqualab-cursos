$(document).ready(function () {
    // Diferenciar "Compra ahora" vs "Agregar al carrito" (el handler del carrito vive en layouts.pages)
    $(document).on('click', '#btnBundleBuyNow', function () { $('#bundleBuyNow').val('1'); });
    $(document).on('click', '#btnBundleAddCart', function () { $('#bundleBuyNow').val(''); });
});
