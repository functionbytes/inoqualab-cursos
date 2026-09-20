(function () {
    var $wrap = $('#orderApproved');
    if (!$wrap.length) return;

    var orderId    = $wrap.data('order-id');
    var value      = parseFloat($wrap.data('order-value')) || 0;
    var currency   = $wrap.data('order-currency');
    var contentIds = $wrap.data('order-content-ids') || [];

    // Disparar la conversión una sola vez por orden (evita recargas/duplicados).
    var key = 'purchase_fired_' + orderId;
    try { if (sessionStorage.getItem(key)) return; sessionStorage.setItem(key, '1'); } catch (e) {}

    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', { value: value, currency: currency, content_ids: contentIds, content_type: 'product', num_items: contentIds.length });
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', { transaction_id: orderId, value: value, currency: currency });
    }
    if (typeof ttq !== 'undefined') {
        ttq.track('CompletePayment', { value: value, currency: currency, content_type: 'product' });
    }
})();
