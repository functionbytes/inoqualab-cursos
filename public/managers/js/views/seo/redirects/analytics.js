$(function () {

    var analyticsUrl = $('#chart-container').data('analytics-url');
    var testUrl = $('#chart-container').data('test-url');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var chartInstance = null;

    // ── Cargar datos del chart ──────────────────────────────────────────────
    function loadChartData() {
        $('#chart-loading').show();
        $('#hits-chart').hide();
        $('#chart-table-fallback').hide();

        $.getJSON(analyticsUrl, function (res) {
            var labels = res.labels || [];
            var data = res.data || [];
            var total = res.total_30d || 0;

            $('#chart-subtitle').text(total + ' hits en los ultimos 30 dias');
            $('#chart-loading').hide();

            if (typeof Chart !== 'undefined') {
                renderChart(labels, data);
            } else {
                renderTable(labels, data);
            }
        }).fail(function () {
            $('#chart-loading').hide();
            toastr.error('Error al cargar los datos de analytics.');
        });
    }

    function renderChart(labels, data) {
        $('#hits-chart').show();

        if (chartInstance) {
            chartInstance.destroy();
        }

        var ctx = document.getElementById('hits-chart').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hits',
                    data: data,
                    backgroundColor: 'rgba(0, 139, 206, 0.2)',
                    borderColor: '#008bce',
                    borderWidth: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
        $('#hits-chart').css('min-height', '260px');
    }

    function renderTable(labels, data) {
        var rows = '';
        for (var i = 0; i < labels.length; i++) {
            rows += '<tr><td>' + labels[i] + '</td><td class="text-end fw-semibold">' + (data[i] || 0) + '</td></tr>';
        }
        $('#chart-table-body').html(rows || '<tr><td colspan="2" class="text-center text-muted py-3">Sin datos</td></tr>');
        $('#chart-table-fallback').show();
    }

    $('#btn-reload-chart').on('click', loadChartData);

    // ── Probar redirect ─────────────────────────────────────────────────────
    $('#btn-test-redirect').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Probando...');

        $.ajax({
            url: testUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#test-result-container').show();
                var statusClass = res.success ? 'text-success' : 'text-danger';
                $('#test-result').html(
                    '<span class="' + statusClass + ' fw-semibold">' +
                    (res.message || 'Test completado') +
                    '</span>'
                );
                toastr.success(res.message || 'Test ejecutado correctamente.');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al probar el redirect.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Probar redirect');
            }
        });
    });

    // ── Init ────────────────────────────────────────────────────────────────
    loadChartData();

});
