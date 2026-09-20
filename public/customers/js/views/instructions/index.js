$(function () {
    var filterUrl = $('#insList').data('filter-url');

    $('.lb-tree .item').on('click', function () {
        $('.lb-tree .item').removeClass('is-active');
        $(this).addClass('is-active');

        var categoryId = $(this).data('category-id');
        var $list = $('#insList');
        $list.css('opacity', .5);

        $.ajax({
            url: filterUrl,
            type: 'GET',
            data: { category_id: categoryId },
            success: function (html) {
                $list.html(html).css('opacity', 1);

                var total = $list.find('.ins-card').length;
                $('#insListSub').text(total + ' ' + (total === 1 ? 'guía disponible' : 'guías disponibles'));
            }
        });
    });
});
