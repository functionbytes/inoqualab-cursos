(function () {
    'use strict';

    var $page = $('#dashboard-index');

    var selectedRange = $page.data('range');
    var ROUTES = $page.data('routes');

    var PRIMARY  = '#008bce';
    var PRIMARY2 = '#0065a0';
    var DARK     = '#333333';

    var _sparks = {};
    var billingChart = null;
    var ordersChart = null;

    // ─── Helpers ──────────────────────────────────────────────────────────
    var fmt = function (n) { return new Intl.NumberFormat('es-ES').format(n || 0); };
    var money = function (n) { return '$' + fmt(Math.round(n || 0)); };

    function cmpBadge(current, previous) {
        if (!previous || previous === 0) {
            return current > 0 ? '' : '';
        }
        var pct  = ((current - previous) / previous * 100).toFixed(1);
        var up   = parseFloat(pct) >= 0;
        var icon = up ? 'fa-arrow-up' : 'fa-arrow-down';
        var bg   = up ? 'bg-success-subtle' : 'bg-danger-subtle';
        var txt  = up ? 'text-success' : 'text-danger';
        var sign = parseFloat(pct) > 0 ? '+' : '';
        return '<div class="d-flex align-items-center">' +
            '<span class="me-1 rounded-circle ' + bg + ' d-flex align-items-center justify-content-center" style="width:20px;height:20px;">' +
                '<i class="fas ' + icon + ' ' + txt + '" style="font-size:0.6rem;"></i>' +
            '</span>' +
            '<p class="text-dark me-1 fs-3 mb-0">' + sign + Math.abs(pct) + '%</p>' +
            '<p class="fs-3 mb-0 text-muted">vs anterior</p>' +
        '</div>';
    }

    // ─── Realtime ──────────────────────────────────────────────────────────
    function loadRealtime() {
        $.get(ROUTES.realtime)
            .done(function (res) {
                if (!res.success) return;
                $('#realtime-count').text(fmt(res.data.active_users));
                var now = new Date();
                $('#realtime-updated').text('Act. ' + now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            })
            .fail(function () { $('#realtime-count').text('—'); });
    }

    // ─── KPI sparklines ──────────────────────────────────────────────────
    function renderSparklines(kpis) {
        var spW = window.innerWidth < 576 ? 60 : 80;

        function mkCfg(data, color) {
            return {
                chart: { type: 'area', height: 60, width: spW, sparkline: { enabled: true }, animations: { enabled: false }, fontFamily: 'inherit' },
                series: [{ data: data }],
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 0, inverseColors: false, opacityFrom: 0.12, opacityTo: 0, stops: [20, 180] },
                },
                colors: [color],
                markers: { size: 0 },
                tooltip: { theme: 'dark', fixed: { enabled: true, position: 'right' }, x: { show: false } },
            };
        }

        var map = {
            '#spark-courses': { data: kpis.courses.series, color: PRIMARY },
            '#spark-enterprises': { data: kpis.enterprises.series, color: DARK },
            '#spark-blogs': { data: kpis.blogs.series, color: PRIMARY2 },
            '#spark-customers': { data: kpis.customers.series, color: PRIMARY },
            '#spark-managers': { data: kpis.managers.series, color: DARK },
        };

        Object.keys(map).forEach(function (sel) {
            var el = document.querySelector(sel);
            if (!el) return;
            var cfg = map[sel];
            if (_sparks[sel]) _sparks[sel].destroy();
            _sparks[sel] = new ApexCharts(el, mkCfg(cfg.data, cfg.color));
            _sparks[sel].render();
        });
    }

    function renderKpis(kpis) {
        var fields = {
            courses: 'kpi-courses',
            enterprises: 'kpi-enterprises',
            blogs: 'kpi-blogs',
            customers: 'kpi-customers',
            managers: 'kpi-managers',
        };

        Object.keys(fields).forEach(function (key) {
            var kpi = kpis[key];
            $('#' + fields[key]).text(fmt(kpi.total));
            $('#cmp-' + key).html(cmpBadge(kpi.current, kpi.previous));
        });

        renderSparklines(kpis);
    }

    // ─── Chart x-axis: fewer labels on narrow screens, no bars ever dropped ──
    function responsiveXAxis(categoriesCount) {
        var w = window.innerWidth;
        if (w >= 992 || categoriesCount <= 1) return {};

        var maxLabels = w < 576 ? 5 : 7;
        return {
            tickAmount: Math.min(maxLabels, categoriesCount - 1),
            labels: { rotate: -45, style: { fontSize: w < 576 ? '10px' : '11px' } },
        };
    }

    // ─── Billing chart ─────────────────────────────────────────────────────
    function renderBillingChart(orders) {
        $('#billing-total').text(money(orders.total_amount));
        $('#billing-agreement').text(fmt(orders.agreement_count));
        $('#billing-online').text(fmt(orders.online_count));

        var options = {
            series: [{ name: 'Ganancias', data: orders.amount_series }],
            chart: { toolbar: { show: false }, type: 'bar', fontFamily: "Plus Jakarta Sans', sans-serif", foreColor: PRIMARY, height: 300 },
            colors: [PRIMARY],
            plotOptions: { bar: { horizontal: false, columnWidth: '45%', borderRadius: 6, borderRadiusApplication: 'end' } },
            dataLabels: { enabled: false },
            legend: { show: false },
            grid: { borderColor: 'rgba(0,0,0,0.1)', strokeDashArray: 3, xaxis: { lines: { show: false } } },
            xaxis: $.extend({ categories: orders.categories, axisBorder: { show: false } }, responsiveXAxis(orders.categories.length)),
            yaxis: { tickAmount: 4 },
            tooltip: { theme: 'dark', y: { formatter: money } },
        };

        if (billingChart) billingChart.destroy();
        billingChart = new ApexCharts(document.querySelector('#billing-chart'), options);
        billingChart.render();
    }

    // ─── Orders chart ──────────────────────────────────────────────────────
    function renderOrdersChart(orders) {
        var options = {
            series: [{ name: 'Ordenes', data: orders.count_series }],
            chart: { toolbar: { show: false }, height: 260, type: 'bar', fontFamily: "Plus Jakarta Sans', sans-serif", foreColor: DARK },
            colors: [PRIMARY2],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            legend: { show: false },
            grid: { yaxis: { lines: { show: false } }, xaxis: { lines: { show: false } } },
            xaxis: $.extend({ categories: orders.categories, axisBorder: { show: false }, axisTicks: { show: false } }, responsiveXAxis(orders.categories.length)),
            yaxis: { labels: { show: false } },
            tooltip: { theme: 'dark' },
        };

        if (ordersChart) ordersChart.destroy();
        ordersChart = new ApexCharts(document.querySelector('#orders-chart'), options);
        ordersChart.render();
    }

    // ─── Overview ──────────────────────────────────────────────────────────
    function loadOverview() {
        $.get(ROUTES.overview, { range: selectedRange })
            .done(function (res) {
                if (!res.success) return;
                renderKpis(res.data.kpis);
                renderBillingChart(res.data.orders);
                renderOrdersChart(res.data.orders);
            });
    }

    // ─── Load all ──────────────────────────────────────────────────────────
    function loadAll() {
        $('#kpi-courses,#kpi-enterprises,#kpi-blogs,#kpi-customers,#kpi-managers')
            .html('<span class="spinner-border spinner-border-sm text-muted"></span>');
        $('#cmp-courses,#cmp-enterprises,#cmp-blogs,#cmp-customers,#cmp-managers').html('');

        loadOverview();
    }

    // ─── Pills scroll fade hint ────────────────────────────────────────────
    function updatePillsFade() {
        var el = document.getElementById('rangePills');
        if (!el) return;
        var hasOverflow = el.scrollWidth > el.clientWidth + 1;
        var atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;
        $(el).closest('.dashboard-pills-col').toggleClass('has-scroll-more', hasOverflow && !atEnd);
    }

    // ─── Events ────────────────────────────────────────────────────────────
    $('#rangePills').on('click', '.nav-link', function (e) {
        e.preventDefault();
        selectedRange = $(this).data('range');
        $('#rangePills .nav-link').removeClass('active').addClass('text-muted');
        $(this).addClass('active').removeClass('text-muted');
        loadAll();
    });

    $('#rangePills').on('scroll', updatePillsFade);
    $(window).on('resize', updatePillsFade);

    loadRealtime();
    setInterval(loadRealtime, 30000);

    $(document).ready(function () {
        loadAll();
        updatePillsFade();
    });
})();
