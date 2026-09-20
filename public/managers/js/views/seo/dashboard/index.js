(function () {
    'use strict';

    var config = $('#seo-dashboard').data('config');
    var trendValues = config.trendValues;
    var trendLabels = config.trendLabels;
    var hasTrend = config.hasTrend;

    var sparkColor = '#008bce';
    var sparkOpts = {
        chart: {
            type: 'area',
            height: 60,
            width: 90,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
        tooltip: { enabled: false },
        colors: [sparkColor],
        series: [{ name: '', data: trendValues.length ? trendValues : [0] }],
    };

    if (hasTrend) {
        ['spark-score', 'spark-total', 'spark-redirects', 'spark-og'].forEach(function (id) {
            var el = document.querySelector('#' + id);
            if (el) { new ApexCharts(el, sparkOpts).render(); }
        });

        // Área principal tendencia
        new ApexCharts(document.querySelector('#trendChart'), {
            series: [{ name: 'Metas actualizadas', data: trendValues }],
            chart: {
                type: 'area',
                height: 260,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'inherit',
            },
            colors: [sparkColor],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02 } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: trendLabels,
                labels: {
                    style: { fontSize: '11px', colors: '#adb5bd' },
                    formatter: function (v) {
                        return v && v.length === 10 ? v.slice(8, 10) + '/' + v.slice(5, 7) : v;
                    },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#adb5bd' },
                    formatter: function (v) { return Math.round(v); },
                },
                min: 0,
            },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { theme: 'light', y: { formatter: function (v) { return v + ' metas'; } } },
        }).render();
    }

    // Donut distribución grados
    var gradeTotal = config.gradeTotal;
    if (gradeTotal > 0) {
        var gd = config.gradeDistribution;
        new ApexCharts(document.querySelector('#gradeDonut'), {
            series: [
                parseInt(gd.A) || 0,
                parseInt(gd.B) || 0,
                parseInt(gd.C) || 0,
                parseInt(gd.D) || 0,
                parseInt(gd.F) || 0,
            ],
            labels: ['A (90+)', 'B (75–89)', 'C (60–74)', 'D (40–59)', 'F (<40)'],
            colors: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#b10100'],
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'inherit',
                toolbar: { show: false },
            },
            plotOptions: {
                pie: { donut: { size: '65%' } },
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '12px' },
            tooltip: {
                theme: 'light',
                y: { formatter: function (v) { return v + ' páginas'; } },
            },
        }).render();
    }

})();
