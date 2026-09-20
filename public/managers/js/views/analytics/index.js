(function () {
    'use strict';

    var $page = $('#analytics-page');

    let selectedRange = $page.data('range');
    let countriesGeoData = null, leafletMap = null, geoLayer = null, countrySessions = {};
    let dailyChart = null, browserChart = null, devicesChart = null, channelChart = null, heatmapChart = null;

    const exportCache = {};

    const ROUTES = $page.data('routes');

    const PRIMARY  = '#008bce';
    const PRIMARY2 = '#0065a0';
    const DARK     = '#333333';
    const PALETTE  = [PRIMARY, DARK, PRIMARY2, '#555', '#7ab8d9', '#aaa'];

    function chartH(type) {
        var w = window.innerWidth;
        if (type === 'main')    return w < 768 ? 220 : 295;
        if (type === 'heatmap') return w < 768 ? 150 : 200;
        if (type === 'donut')   return w < 576 ? 160 : 200;
        return 200;
    }

    const channelColors = {
        organic: PRIMARY, cpc: DARK, social: PRIMARY2,
        referral: '#555', email: '#c41c1c', direct: '#888',
        none: '#888', '(none)': '#888'
    };

    // ─── Helpers ──────────────────────────────────────────────────────────
    const fmt = n => new Intl.NumberFormat('es-ES').format(n || 0);

    function fmtSeconds(s) {
        s = Math.round(s || 0);
        const m = Math.floor(s / 60), sec = s % 60;
        return m + 'm ' + String(sec).padStart(2, '0') + 's';
    }

    function escHtml(t) {
        const d = document.createElement('div');
        d.textContent = String(t || '');
        return d.innerHTML;
    }

    const spinner    = () => '<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-muted"></span></div>';
    const emptyState = m  => `<div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i><small>${escHtml(m)}</small></div>`;

    function cmpBadge(current, previous, invert) {
        invert = invert || false;
        if (!previous || previous === 0) return '';
        const pct  = ((current - previous) / previous * 100).toFixed(1);
        const up   = invert ? parseFloat(pct) < 0 : parseFloat(pct) > 0;
        const icon = up ? 'fa-arrow-up' : 'fa-arrow-down';
        const bg   = up ? 'bg-success-subtle' : 'bg-danger-subtle';
        const txt  = up ? 'text-success'      : 'text-danger';
        const sign = parseFloat(pct) > 0 ? '+' : '';
        return '<div class="d-flex align-items-center">' +
            '<span class="me-1 rounded-circle ' + bg + ' d-flex align-items-center justify-content-center" style="width:20px;height:20px;">' +
                '<i class="fas ' + icon + ' ' + txt + '" style="font-size:0.6rem;"></i>' +
            '</span>' +
            '<p class="text-dark me-1 fs-3 mb-0">' + sign + Math.abs(pct) + '%</p>' +
            '<p class="fs-3 mb-0 text-muted">vs anterior</p>' +
        '</div>';
    }

    // ─── Pagination ────────────────────────────────────────────────────────
    var PER_PAGE = 10;
    var pagerState  = {};
    var pagerRenders = {};

    function getPager(key) {
        if (!pagerState[key]) pagerState[key] = { data: [], page: 1 };
        return pagerState[key];
    }
    function setPagerData(key, data) { getPager(key).data = data; getPager(key).page = 1; }
    function getPageItems(key) {
        var s = getPager(key);
        return s.data.slice((s.page - 1) * PER_PAGE, s.page * PER_PAGE);
    }

    function pagerNav(key) {
        var s = getPager(key);
        var total = s.data.length;
        var totalPages = Math.ceil(total / PER_PAGE);
        if (totalPages <= 1) return '';
        var page  = s.page;
        var from  = (page - 1) * PER_PAGE + 1;
        var to    = Math.min(page * PER_PAGE, total);
        var start = Math.max(1, page - 2);
        var end   = Math.min(totalPages, start + 4);

        function btn(p, label, disabled, active) {
            var base  = 'pager-btn d-inline-flex align-items-center justify-content-center border-0 rounded';
            var style = active
                ? 'background:' + PRIMARY + ';color:#fff;font-weight:600;'
                : (disabled ? 'background:transparent;color:#ccc;cursor:default;' : 'background:transparent;color:#555;');
            return '<button class="' + base + '" style="width:30px;height:30px;font-size:0.8rem;' + style + '"' +
                   ' data-pkey="' + key + '" data-page="' + p + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
        }

        var btns = btn(page - 1, '&#8249;', page <= 1, false);
        for (var i = start; i <= end; i++) btns += btn(i, i, false, i === page);
        btns += btn(page + 1, '&#8250;', page >= totalPages, false);

        return '<div class="d-flex align-items-center justify-content-between pt-1">' +
               '<p class="text-muted">' + from + '–' + to + ' de ' + total + '</p>' +
               '<div class="d-flex align-items-center gap-1">' + btns + '</div></div>';
    }

    $(document).on('click', '.pager-btn', function (e) {
        e.preventDefault();
        var key  = $(this).data('pkey');
        var page = parseInt($(this).data('page'));
        if (!pagerState[key] || pagerState[key].page === page) return;
        pagerState[key].page = page;
        pagerRenders[key]();
    });

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

    // ─── Overview + KPIs ──────────────────────────────────────────────────
    var currentTotals = null;

    function loadOverview() {
        $.get(ROUTES.overview, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { setKpiError(); return; }
                var t = res.data.totals;
                currentTotals = t;
                exportCache['overview'] = res.data;

                $('#kpi-sessions').text(fmt(t.sessions));
                $('#kpi-users').text(fmt(t.totalUsers));
                $('#kpi-pageviews').text(fmt(t.screenPageViews));
                $('#kpi-bounce').text(((t.bounceRate || 0) * 100).toFixed(1) + '%');

                renderDailyChart(res.data.chart_data);
                renderSparklines(res.data.chart_data);
                loadComparison();
            })
            .fail(setKpiError);
    }

    function setKpiError() {
        ['sessions', 'users', 'pageviews', 'bounce'].forEach(function(k) { $('#kpi-' + k).text('—'); });
    }

    function loadComparison() {
        if (!currentTotals) return;
        $.get(ROUTES.comparison, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !currentTotals) return;
                var p = res.data;
                $('#cmp-sessions').html(cmpBadge(currentTotals.sessions, p.sessions));
                $('#cmp-users').html(cmpBadge(currentTotals.totalUsers, p.totalUsers));
                $('#cmp-pageviews').html(cmpBadge(currentTotals.screenPageViews, p.screenPageViews));
                $('#cmp-bounce').html(cmpBadge(currentTotals.bounceRate, p.bounceRate, true));
            });
    }

    function loadSessionMetrics() {
        $.get(ROUTES.sessionMetrics, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#kpi-new-users, #kpi-duration').text('—'); return; }
                $('#kpi-new-users').text(fmt(res.data.new_users));
                $('#kpi-duration').text(fmtSeconds(res.data.avg_session_duration));
            })
            .fail(function () { $('#kpi-new-users, #kpi-duration').text('—'); });
    }

    // ─── Daily chart ──────────────────────────────────────────────────────
    function renderDailyChart(chartData) {
        if (!chartData || !chartData.length) {
            $('#daily-chart').html(emptyState('Sin datos para el período'));
            return;
        }
        var months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        var fmtDate = function(d) {
            if (!d || d.length !== 8) return d;
            return months[parseInt(d.slice(4, 6)) - 1] + ' ' + parseInt(d.slice(6, 8));
        };
        var dates    = chartData.map(function(r) { return fmtDate(r.date || ''); });
        var sessions = chartData.map(function(r) { return parseInt(r.sessions || 0); });
        var views    = chartData.map(function(r) { return parseInt(r.screenPageViews || 0); });

        if (dailyChart) { dailyChart.destroy(); dailyChart = null; }
        $('#daily-chart').html('');

        dailyChart = new ApexCharts(document.querySelector('#daily-chart'), {
            series: [
                { name: 'Sesiones', data: sessions },
                { name: 'Vistas de página', data: views },
            ],
            chart: { type: 'area', height: chartH('main'), toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
            colors: [PRIMARY, DARK],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.02, stops: [0, 100] } },
            xaxis: {
                categories: dates,
                labels: { rotate: -30, style: { fontSize: '11px', colors: '#adb5bd' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#adb5bd' }, formatter: function(v) { return Math.round(v); } } },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { theme: 'light', shared: true, intersect: false },
            legend: { position: 'top', horizontalAlign: 'right', fontFamily: 'inherit' },
            markers: { size: 0 },
        });
        dailyChart.render();
    }

    // ─── Sparklines ────────────────────────────────────────────────────────
    var _sparks = {};

    function renderSparklines(chartData) {
        if (!chartData || !chartData.length) return;

        var spW = window.innerWidth < 576 ? 55 : 80;
        function mkCfg(data, color, type) {
            return {
                series: [{ data: data }],
                chart: { type: type, height: 70, width: spW, sparkline: { enabled: true }, animations: { enabled: false }, fontFamily: 'inherit' },
                colors: [color],
                stroke: { curve: 'smooth', width: 2 },
                fill: type === 'area'
                    ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } }
                    : { type: 'solid' },
                tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: function() { return ''; } } } },
                plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
            };
        }

        var defs = {
            '#spark-sessions':  { data: chartData.map(function(r) { return r.sessions  || 0; }),                                      color: PRIMARY,  type: 'area' },
            '#spark-users':     { data: chartData.map(function(r) { return r.totalUsers || 0; }),                                      color: DARK,     type: 'bar'  },
            '#spark-pageviews': { data: chartData.map(function(r) { return r.screenPageViews || 0; }),                                 color: PRIMARY2, type: 'bar'  },
            '#spark-bounce':    { data: chartData.map(function(r) { return parseFloat(((r.bounceRate || 0) * 100).toFixed(1)); }),     color: '#555',   type: 'area' },
        };

        Object.keys(defs).forEach(function(sel) {
            var cfg = defs[sel];
            var el = document.querySelector(sel);
            if (!el) return;
            if (_sparks[sel]) _sparks[sel].destroy();
            _sparks[sel] = new ApexCharts(el, mkCfg(cfg.data, cfg.color, cfg.type));
            _sparks[sel].render();
        });
    }

    // ─── Channel trend ─────────────────────────────────────────────────────
    function loadChannelTrend() {
        $.get(ROUTES.channelTrend, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.dates || !res.data.dates.length) {
                    $('#channel-trend-chart').html(emptyState('Sin datos de canales'));
                    return;
                }
                var d = res.data;
                if (channelChart) { channelChart.destroy(); channelChart = null; }
                $('#channel-trend-chart').html('');

                channelChart = new ApexCharts(document.querySelector('#channel-trend-chart'), {
                    series: d.series,
                    chart: { type: 'area', height: chartH('main'), stacked: true, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: d.series.map(function(s) { return channelColors[s.name.toLowerCase()] || '#adb5bd'; }),
                    stroke: { curve: 'smooth', width: 1 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: d.dates, labels: { rotate: -30, style: { fontSize: '11px' } } },
                    yaxis: { labels: { formatter: function(v) { return Math.round(v); } } },
                    grid: { borderColor: '#f0f0f0' },
                    tooltip: { theme: 'light', shared: true },
                    legend: { position: 'top' },
                });
                channelChart.render();
            })
            .fail(function() { $('#channel-trend-chart').html(emptyState('Sin datos')); });
    }

    // ─── Hourly heatmap ────────────────────────────────────────────────────
    function loadHourlyHeatmap() {
        $.get(ROUTES.hourlyHeatmap, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#heatmap-chart').html(emptyState('Sin datos')); return; }
                var matrix = res.data.matrix, days = res.data.days;
                if (!matrix) return;

                var series = days.map(function(day, dayIdx) {
                    return {
                        name: day,
                        data: matrix[dayIdx].map(function(val, hour) {
                            return { x: String(hour).padStart(2, '0') + 'h', y: val };
                        })
                    };
                });

                if (heatmapChart) { heatmapChart.destroy(); heatmapChart = null; }
                $('#heatmap-chart').html('');

                heatmapChart = new ApexCharts(document.querySelector('#heatmap-chart'), {
                    series: series,
                    chart: { type: 'heatmap', height: chartH('heatmap'), toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    colors: [PRIMARY],
                    xaxis: { labels: { style: { fontSize: '10px' } } },
                    tooltip: { theme: 'light', y: { formatter: function(v) { return fmt(v) + ' sesiones'; } } },
                    legend: { show: false },
                });
                heatmapChart.render();
            })
            .fail(function() { $('#heatmap-chart').html(emptyState('Sin datos')); });
    }

    // ─── Map ───────────────────────────────────────────────────────────────
    var countryNameMap = {
        'United States': 'United States of America',
        'Czech Republic': 'Czechia',
        'Tanzania': 'United Republic of Tanzania',
    };

    var countryFlags = {
        'Argentina':'🇦🇷','Australia':'🇦🇺','Austria':'🇦🇹','Belgium':'🇧🇪','Bolivia':'🇧🇴',
        'Brazil':'🇧🇷','Canada':'🇨🇦','Chile':'🇨🇱','China':'🇨🇳','Colombia':'🇨🇴',
        'Costa Rica':'🇨🇷','Cuba':'🇨🇺','Denmark':'🇩🇰','Dominican Republic':'🇩🇴','Ecuador':'🇪🇨',
        'Egypt':'🇪🇬','El Salvador':'🇸🇻','France':'🇫🇷','Germany':'🇩🇪','Guatemala':'🇬🇹',
        'Honduras':'🇭🇳','India':'🇮🇳','Indonesia':'🇮🇩','Ireland':'🇮🇪','Italy':'🇮🇹',
        'Japan':'🇯🇵','Malaysia':'🇲🇾','Mexico':'🇲🇽','Morocco':'🇲🇦','Netherlands':'🇳🇱',
        'New Zealand':'🇳🇿','Nicaragua':'🇳🇮','Nigeria':'🇳🇬','Norway':'🇳🇴','Pakistan':'🇵🇰',
        'Panama':'🇵🇦','Paraguay':'🇵🇾','Peru':'🇵🇪','Philippines':'🇵🇭','Poland':'🇵🇱',
        'Portugal':'🇵🇹','Romania':'🇷🇴','Russia':'🇷🇺','Saudi Arabia':'🇸🇦','Spain':'🇪🇸',
        'Sweden':'🇸🇪','Switzerland':'🇨🇭','Turkey':'🇹🇷','Ukraine':'🇺🇦',
        'United Arab Emirates':'🇦🇪','United Kingdom':'🇬🇧','United States':'🇺🇸',
        'Uruguay':'🇺🇾','Venezuela':'🇻🇪','Vietnam':'🇻🇳',
    };

    var countryLayers = {};

    function getColor(sessions, max) {
        if (!sessions) return '#f0f0f0';
        var r = sessions / max;
        if (r > 0.75) return '#005b9f';
        if (r > 0.5)  return PRIMARY;
        if (r > 0.25) return PRIMARY2;
        if (r > 0.1)  return '#5ba5d5';
        if (r > 0.02) return '#a8d0ed';
        return '#dbeef9';
    }

    function initMap() {
        if (leafletMap) { leafletMap.remove(); leafletMap = null; }
        leafletMap = L.map('visits-map', { zoomControl: false, scrollWheelZoom: false, attributionControl: false }).setView([20, 0], 1.5);
        L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);

        $('#mapResetBtn').on('click', function () {
            leafletMap.flyTo([20, 0], 1.5, { duration: 0.8 });
            $('.country-item').removeClass('country-active');
        });
    }

    function zoomToCountry(apiName) {
        var geoName = countryNameMap[apiName] || apiName;
        var layer   = countryLayers[geoName] || countryLayers[apiName];
        if (!layer) return;
        leafletMap.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 0.9, maxZoom: 6 });
        $('.country-item').removeClass('country-active');
        $('.country-item[data-country="' + CSS.escape(apiName) + '"]').addClass('country-active');
    }

    function drawGeoLayer(geoJson, maxSessions) {
        if (geoLayer) leafletMap.removeLayer(geoLayer);
        Object.keys(countryLayers).forEach(function(k) { delete countryLayers[k]; });

        geoLayer = L.geoJson(geoJson, {
            style: function(feature) {
                var name = feature.properties.ADMIN || feature.properties.name;
                return { fillColor: getColor(countrySessions[name] || 0, maxSessions), weight: 0.5, color: '#fff', fillOpacity: 0.85 };
            },
            onEachFeature: function(feature, layer) {
                var name     = feature.properties.ADMIN || feature.properties.name;
                var sessions = countrySessions[name] || 0;
                countryLayers[name] = layer;

                var tip = sessions
                    ? '<strong>' + name + '</strong><br><span style="color:' + PRIMARY + '">' + fmt(sessions) + '</span> sesiones'
                    : '<strong>' + name + '</strong><br><span class="text-muted">Sin visitas</span>';
                layer.bindTooltip(tip, { sticky: true });

                layer.on('mouseover', function () {
                    if (sessions) this.setStyle({ weight: 2, color: PRIMARY, fillOpacity: 1 });
                });
                layer.on('mouseout', function () { geoLayer.resetStyle(this); });
                layer.on('click', function () {
                    if (!sessions) return;
                    leafletMap.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 0.9, maxZoom: 6 });
                    var apiName = Object.keys(countryNameMap).find(function(k) { return countryNameMap[k] === name; }) || name;
                    $('.country-item').removeClass('country-active');
                    $('.country-item[data-country="' + CSS.escape(apiName) + '"]').addClass('country-active');
                });
            }
        }).addTo(leafletMap);
    }

    function loadCountries() {
        $('#countries-list').html(spinner());
        $.get(ROUTES.countries, { range: selectedRange })
            .done(function (res) {
                if (!res.success) return;
                exportCache['countries'] = res.data;
                countrySessions = {};
                res.data.forEach(function(d) {
                    countrySessions[countryNameMap[d.country] || d.country] = d.sessions;
                    countrySessions[d.country] = d.sessions;
                });
                var maxSessions = res.data.length ? Math.max.apply(null, res.data.map(function(d) { return d.sessions; })) : 1;

                if (countriesGeoData) drawGeoLayer(countriesGeoData, maxSessions);
                else {
                    $.getJSON($page.data('geojson-url'))
                        .done(function(geoJson) { countriesGeoData = geoJson; drawGeoLayer(geoJson, maxSessions); })
                        .fail(function() { $('#visits-map').html('<div class="text-center py-5 text-muted">No se pudo cargar el mapa</div>'); });
                }

                var rankColor = function(i) { return i === 0 ? PRIMARY : i === 1 ? PRIMARY2 : i === 2 ? '#7ab8d9' : '#ccc'; };
                var html = res.data.slice(0, 15).map(function(c, i) {
                    return '<div class="country-item" data-country="' + escHtml(c.country) + '">' +
                        '<span class="fw-bold flex-shrink-0 text-end" style="font-size:0.75rem;width:18px;color:' + rankColor(i) + ';">' + (i + 1) + '</span>' +
                        '<span style="font-size:1.2rem;">' + (countryFlags[c.country] || '🌐') + '</span>' +
                        '<div style="min-width:0;flex:1;">' +
                            '<div class="fw-semibold text-truncate mb-1" style="font-size:0.82rem;">' + escHtml(c.country) + '</div>' +
                            '<div class="progress rounded-pill" style="height:5px;">' +
                                '<div class="progress-bar rounded-pill" style="width:' + c.percentage + '%;background:' + PRIMARY + ';"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="text-end flex-shrink-0">' +
                            '<div class="fw-bold lh-1" style="font-size:0.9rem;color:' + PRIMARY + ';">' + fmt(c.sessions) + '</div>' +
                            '<div class="text-muted lh-1 mt-1" style="font-size:0.7rem;">' + c.percentage + '%</div>' +
                        '</div>' +
                    '</div>';
                }).join('');
                $('#countries-list').html(html);

                $(document).on('click', '.country-item', function () {
                    zoomToCountry($(this).data('country'));
                });
            });
    }

    // ─── Devices ───────────────────────────────────────────────────────────
    var deviceIcons  = { desktop: 'fas fa-desktop', mobile: 'fas fa-mobile-alt', tablet: 'fas fa-tablet-alt' };
    var deviceLabels = { desktop: 'Escritorio', mobile: 'Móvil', tablet: 'Tablet' };

    function loadDevices() {
        $('#devices-chart').html(spinner()); $('#devices-list').html('');
        $.get(ROUTES.devices, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#devices-chart').html(emptyState('Sin datos')); return; }
                var data = res.data;

                if (devicesChart) { devicesChart.destroy(); devicesChart = null; }
                $('#devices-chart').html('');
                devicesChart = new ApexCharts(document.querySelector('#devices-chart'), {
                    series: data.map(function(d) { return d.sessions; }),
                    labels: data.map(function(d) { return d.device; }),
                    chart: { type: 'donut', height: chartH('donut'), fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function(v) { return fmt(v); } } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                devicesChart.render();

                $('#devices-list').html(data.map(function(d, i) {
                    var key   = d.device.toLowerCase();
                    var icon  = deviceIcons[key]  || 'fas fa-question-circle';
                    var label = deviceLabels[key] || escHtml(d.device);
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < data.length - 1 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + label + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(d.sessions) + ' sesiones</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + d.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#devices-chart').html(emptyState('Error al cargar')); });
    }

    // ─── Browsers ──────────────────────────────────────────────────────────
    var browserIconMap = {
        chrome: 'fab fa-chrome', safari: 'fab fa-safari',
        firefox: 'fab fa-firefox-browser', edge: 'fab fa-edge',
        opera: 'fab fa-opera', samsung: 'fas fa-mobile-alt',
    };

    function loadBrowsers() {
        $('#browser-chart').html(spinner()); $('#browser-list').html('');
        $.get(ROUTES.browsers, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#browser-chart').html(emptyState('Sin datos')); return; }
                var data = res.data;

                if (browserChart) { browserChart.destroy(); browserChart = null; }
                $('#browser-chart').html('');
                browserChart = new ApexCharts(document.querySelector('#browser-chart'), {
                    series: data.map(function(b) { return b.sessions; }),
                    labels: data.map(function(b) { return b.name; }),
                    chart: { type: 'donut', height: chartH('donut'), fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function(v) { return fmt(v); } } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                browserChart.render();

                $('#browser-list').html(data.slice(0, 5).map(function(b, i) {
                    var key  = b.name.toLowerCase().replace(/\s+/g, '');
                    var icon = browserIconMap[key] || 'fas fa-globe';
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < 4 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + escHtml(b.name) + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(b.sessions) + ' vistas</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + b.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#browser-chart').html(emptyState('Error al cargar')); });
    }

    // ─── OS ────────────────────────────────────────────────────────────────
    var osIconMap = {
        windows: 'fab fa-windows', android: 'fab fa-android',
        ios: 'fab fa-apple', macos: 'fab fa-apple',
        linux: 'fab fa-linux', chrome: 'fab fa-chrome',
    };

    function loadOS() {
        $('#os-list').html(spinner());
        $.get(ROUTES.os, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#os-list').html(emptyState('Sin datos')); return; }
                $('#os-list').html(res.data.map(function(o, i) {
                    var key  = o.os.toLowerCase().replace(/\s+/g, '');
                    var icon = osIconMap[key] || 'fas fa-laptop';
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < res.data.length - 1 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + escHtml(o.os) + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(o.sessions) + ' sesiones</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + o.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#os-list').html(emptyState('Error al cargar')); });
    }

    // ─── Traffic sources ───────────────────────────────────────────────────
    var termBadges = ['bg-primary-subtle text-primary','bg-success-subtle text-success','bg-warning-subtle text-warning','bg-info-subtle text-info','bg-danger-subtle text-danger'];

    function loadTrafficSources() {
        $('#traffic-sources-list').html(spinner()); $('#traffic-sources-pager').html('');
        $.get(ROUTES.trafficSources, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#traffic-sources-list').html(emptyState('Sin datos')); return; }
                exportCache['channels'] = res.data;
                setPagerData('trafficSources', res.data);
                pagerRenders['trafficSources'] = function () {
                    var rows = getPageItems('trafficSources').map(function(s, i) {
                        var badge = termBadges[i % termBadges.length];
                        var label = s.source === '(direct)' ? 'Directo' : escHtml(s.source);
                        var med   = (s.medium === 'none' || s.medium === '(none)' || !s.medium) ? 'directo' : escHtml(s.medium);
                        var num   = (getPager('trafficSources').page - 1) * PER_PAGE + i + 1;
                        return '<tr>' +
                            '<td class="ps-0">' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<span class="badge bg-secondary-subtle text-secondary">' + num + '</span>' +
                                    '<div>' +
                                        '<div class="fw-semibold small">' + label + '</div>' +
                                        '<p class="text-muted">' + med + '</p>' +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(s.sessions) + '</span></td>' +
                            '<td>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<div class="progress progress-thin flex-fill" style="min-width:50px;">' +
                                        '<div class="progress-bar bg-primary" style="width:' + s.percentage + '%;"></div>' +
                                    '</div>' +
                                    '<p class="text-muted">' + s.percentage + '%</p>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    }).join('');
                    $('#traffic-sources-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Fuente</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Participación</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#traffic-sources-pager').html(pagerNav('trafficSources'));
                };
                pagerRenders['trafficSources']();
            })
            .fail(function() { $('#traffic-sources-list').html(emptyState('Sin datos')); });
    }

    // ─── Pages helper ──────────────────────────────────────────────────────
    function pagesTable(rows, cols, headers) {
        var thead = '<thead><tr class="text-muted fw-semibold">' + headers.map(function(h, i) {
            return '<th scope="col" class="' + (i === 0 ? 'ps-0' : '') + (h.end ? ' text-end' : '') + '">' + h.label + '</th>';
        }).join('') + '</tr></thead>';
        var body  = rows.map(function(r) {
            return '<tr>' + cols.map(function(c, i) {
                return '<td class="' + (i === 0 ? 'ps-0' : '') + (c.end ? ' text-end' : '') + '">' + c.render(r) + '</td>';
            }).join('') + '</tr>';
        }).join('');
        return '<table class="table align-middle mb-0 text-nowrap">' + thead + '<tbody class="border-top">' + body + '</tbody></table>';
    }

    function loadTopPages() {
        $('#pages-list').html(spinner()); $('#pages-pager').html('');
        $.get(ROUTES.topPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#pages-list').html(emptyState('Sin datos')); return; }
                exportCache['pages'] = res.data;
                setPagerData('topPages', res.data);
                pagerRenders['topPages'] = function () {
                    $('#pages-list').html(pagesTable(
                        getPageItems('topPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold text-truncate" style="max-width:400px;">' + escHtml(p.title) + '</h6><span class="fs-2 text-muted text-truncate d-block" style="max-width:400px;">' + escHtml(p.url) + '</span>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-primary-subtle text-primary rounded-pill">' + fmt(p.views) + '</span>'; } },
                        ],
                        [{ label: 'Página' }, { label: 'Vistas', end: true }]
                    ));
                    $('#pages-pager').html(pagerNav('topPages'));
                };
                pagerRenders['topPages']();
            });
    }

    function loadLandingPages() {
        $('#landing-pages-list').html(spinner()); $('#landing-pages-pager').html('');
        $.get(ROUTES.landingPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#landing-pages-list').html(emptyState('Sin datos')); return; }
                setPagerData('landingPages', res.data);
                pagerRenders['landingPages'] = function () {
                    $('#landing-pages-list').html(pagesTable(
                        getPageItems('landingPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold small text-truncate" style="max-width:400px;">' + escHtml(p.page) + '</h6>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-success-subtle text-success rounded-pill">' + fmt(p.sessions) + '</span>'; } },
                            { end: true, render: function(p) { return '<p class="text-muted">' + p.bounce_rate + '%</p>'; } },
                        ],
                        [{ label: 'Página de entrada' }, { label: 'Sesiones', end: true }, { label: 'Rebote', end: true }]
                    ));
                    $('#landing-pages-pager').html(pagerNav('landingPages'));
                };
                pagerRenders['landingPages']();
            });
    }

    function loadExitPages() {
        $('#exit-pages-list').html(spinner()); $('#exit-pages-pager').html('');
        $.get(ROUTES.exitPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#exit-pages-list').html(emptyState('Sin datos')); return; }
                setPagerData('exitPages', res.data);
                pagerRenders['exitPages'] = function () {
                    $('#exit-pages-list').html(pagesTable(
                        getPageItems('exitPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold small text-truncate" style="max-width:400px;">' + escHtml(p.page) + '</h6>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-warning-subtle text-warning rounded-pill">' + fmt(p.sessions) + '</span>'; } },
                            { end: true, render: function(p) { return '<p class="text-muted">' + fmt(p.pageviews) + '</p>'; } },
                        ],
                        [{ label: 'Página de salida' }, { label: 'Sesiones', end: true }, { label: 'Vistas', end: true }]
                    ));
                    $('#exit-pages-pager').html(pagerNav('exitPages'));
                };
                pagerRenders['exitPages']();
            });
    }

    // ─── Referrers ─────────────────────────────────────────────────────────
    var mediumBg    = { organic:'bg-success-subtle', cpc:'bg-primary-subtle', social:'bg-info-subtle', referral:'bg-warning-subtle', email:'bg-danger-subtle', direct:'bg-secondary-subtle', none:'bg-secondary-subtle' };
    var mediumColor = { organic:'text-success', cpc:'text-primary', social:'text-info', referral:'text-warning', email:'text-danger', direct:'text-secondary', none:'text-secondary' };
    var mediumLabel = { organic:'Búsqueda orgánica', cpc:'Publicidad CPC', social:'Redes sociales', referral:'Referencia', email:'Email marketing', direct:'Acceso directo', none:'Acceso directo' };
    var mediumIcons = { organic:'fas fa-search', cpc:'fas fa-ad', social:'fas fa-share-alt', referral:'fas fa-link', email:'fas fa-envelope', direct:'fas fa-arrow-right', none:'fas fa-arrow-right' };

    function loadReferrers() {
        $('#referrers-list').html(spinner()); $('#referrers-pager').html('');
        $.get(ROUTES.topReferrers, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#referrers-list').html(emptyState('Sin datos')); return; }
                setPagerData('referrers', res.data);
                pagerRenders['referrers'] = function () {
                    var items = getPageItems('referrers').map(function(r, i) {
                        var bg    = 'bg-secondary-subtle';
                        var color = 'text-secondary';
                        var icon  = 'fas fa-arrow-right';
                        var label = (!r.url || r.url === '(direct)') ? 'Directo' : escHtml(r.url);
                        var isLast = i === getPageItems('referrers').length - 1;
                        return '<div class="d-flex align-items-center justify-content-between ' + (isLast ? '' : 'mb-4') + '">' +
                            '<div class="d-flex align-items-center gap-3">' +
                                '<div class="p-2 ' + bg + ' rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;flex-shrink:0;">' +
                                    '<i class="' + icon + ' ' + color + '"></i>' +
                                '</div>' +
                                '<div>' +
                                    '<h6 class="mb-0 fw-semibold text-truncate" style="max-width:300px;">' + label + '</h6>' +
                                '</div>' +
                            '</div>' +
                            '<h6 class="mb-0 fw-semibold">' + fmt(r.views) + '</h6>' +
                        '</div>';
                    }).join('');
                    $('#referrers-list').html(items);
                    $('#referrers-pager').html(pagerNav('referrers'));
                };
                pagerRenders['referrers']();
            });
    }

    // ─── Search terms ──────────────────────────────────────────────────────
    function loadSearchTerms() {
        $('#search-terms-list').html(spinner()); $('#search-terms-pager').html('');
        $.get(ROUTES.searchTerms, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $('#search-terms-list').html(emptyState('Sin datos de búsquedas'));
                    return;
                }
                setPagerData('searchTerms', res.data);
                pagerRenders['searchTerms'] = function () {
                    var rows = getPageItems('searchTerms').map(function(t, i) {
                        var badge = termBadges[i % termBadges.length];
                        var num   = (getPager('searchTerms').page - 1) * PER_PAGE + i + 1;
                        return '<tr>' +
                            '<td class="ps-0">' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<span class="badge bg-secondary-subtle text-secondary">' + num + '</span>' +
                                    '<span class="fw-semibold">' + escHtml(t.term) + '</span>' +
                                '</div>' +
                            '</td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(t.sessions) + '</span></td>' +
                            '<td><p class="text-muted">' + fmt(t.pageviews) + '</p></td>' +
                            '<td>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<div class="progress progress-thin flex-fill" style="min-width:50px;">' +
                                        '<div class="progress-bar bg-info" style="width:' + t.percentage + '%;"></div>' +
                                    '</div>' +
                                    '<p class="text-muted">' + t.percentage + '%</p>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    }).join('');
                    $('#search-terms-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Término</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Vistas</th>' +
                                '<th scope="col">Participación</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#search-terms-pager').html(pagerNav('searchTerms'));
                };
                pagerRenders['searchTerms']();
            })
            .fail(function() { $('#search-terms-list').html(emptyState('Sin datos')); });
    }

    // ─── User flow ─────────────────────────────────────────────────────────
    function loadUserFlow() {
        $('#user-flow-list').html(spinner()); $('#user-flow-pager').html('');
        $.get(ROUTES.userFlow, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $('#user-flow-list').html(emptyState('Sin datos de flujo'));
                    return;
                }
                setPagerData('userFlow', res.data);
                pagerRenders['userFlow'] = function () {
                    var rows = getPageItems('userFlow').map(function(r, i) {
                        var badge = termBadges[i % termBadges.length];
                        return '<tr>' +
                            '<td class="ps-0 text-truncate" style="max-width:180px;"><div class="small text-truncate">' + escHtml(r.landing) + '</div></td>' +
                            '<td class="text-truncate" style="max-width:180px;"><div class="small text-truncate">' + escHtml(r.exit) + '</div></td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(r.sessions) + '</span></td>' +
                            '<td><p class="text-muted">' + r.bounce_rate + '%</p></td>' +
                        '</tr>';
                    }).join('');
                    $('#user-flow-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Entrada</th>' +
                                '<th scope="col">Salida</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Rebote</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#user-flow-pager').html(pagerNav('userFlow'));
                };
                pagerRenders['userFlow']();
            })
            .fail(function() { $('#user-flow-list').html(emptyState('Sin datos')); });
    }

    // ─── Export ────────────────────────────────────────────────────────────
    function convertToCSV(data) {
        var rows = Array.isArray(data) ? data : (data.rows || data.chart_data || data.data || []);
        if (!rows.length) return '';
        var headers = Object.keys(rows[0]);
        return [
            headers.join(','),
        ].concat(rows.map(function(row) {
            return headers.map(function(h) {
                var val = row[h] != null ? row[h] : '';
                var str = String(val).replace(/"/g, '""');
                return (str.indexOf(',') >= 0 || str.indexOf('"') >= 0 || str.indexOf('\n') >= 0) ? '"' + str + '"' : str;
            }).join(',');
        })).join('\r\n');
    }

    window.exportData = function (type, format) {
        format = format || 'json';
        var data = exportCache[type];
        if (!data) { alert('Primero carga los datos del dashboard.'); return; }

        var blob, filename;
        var today = new Date().toISOString().slice(0, 10);
        if (format === 'csv') {
            blob     = new Blob([convertToCSV(data)], { type: 'text/csv;charset=utf-8;' });
            filename = 'analytics_' + type + '_' + selectedRange + '_' + today + '.csv';
        } else {
            blob     = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            filename = 'analytics_' + type + '_' + selectedRange + '_' + today + '.json';
        }

        var url = URL.createObjectURL(blob);
        var a   = document.createElement('a');
        a.href = url; a.download = filename; a.click();
        URL.revokeObjectURL(url);
    };

    $(document).on('click', '.btn-print', function () {
        window.print();
    });

    $(document).on('click', '.btn-export', function () {
        exportData($(this).data('key'), $(this).data('format'));
    });

    // ─── Load all ──────────────────────────────────────────────────────────
    function loadAll() {
        currentTotals = null;
        $('#kpi-sessions,#kpi-users,#kpi-pageviews,#kpi-bounce,#kpi-new-users,#kpi-duration')
            .html('<span class="spinner-border spinner-border-sm text-muted"></span>');
        $('#cmp-sessions,#cmp-users,#cmp-pageviews,#cmp-bounce').html('');
        $('#export-period-label').text($('#rangePills .nav-link.active').text().trim());

        loadOverview();
        loadSessionMetrics();
        loadBrowsers();
        loadDevices();
        loadOS();
        loadCountries();
        loadTrafficSources();
        loadTopPages();
        loadLandingPages();
        loadExitPages();
        loadReferrers();
        loadChannelTrend();
        loadHourlyHeatmap();
        loadSearchTerms();
        loadUserFlow();
    }

    // ─── Events ────────────────────────────────────────────────────────────
    $('#rangePills').on('click', '.nav-link', function (e) {
        e.preventDefault();
        selectedRange = $(this).data('range');
        $('#rangePills .nav-link').removeClass('active').addClass('text-muted');
        $(this).addClass('active').removeClass('text-muted');
        loadAll();
    });

    $('#refreshBtn').on('click', loadAll);

    loadRealtime();
    setInterval(loadRealtime, 30000);

    $(document).ready(function () {
        initMap();
        loadAll();
    });

})();
