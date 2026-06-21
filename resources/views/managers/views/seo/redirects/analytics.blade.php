@extends('layouts.managers')

@section('title', 'Analytics — ' . $seoRedirect->source_path)

@section('content')


    <div class="row g-4">

        {{-- Info card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold">Estadisticas de redirect</h5>
                            <p class="mb-0 text-muted small">Actividad de hits para esta redirección</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-test-redirect">
                                Probar redirect
                            </button>
                            <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-secondary">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">URL de origen</p>
                            <code class="fs-6 text-break">{{ $seoRedirect->source_path }}</code>
                        </div>
                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">URL de destino</p>
                            <code class="fs-6 text-break">{{ $seoRedirect->target_path }}</code>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Codigo</p>
                            <span class="badge fs-6 {{ $seoRedirect->status_code === 301 ? 'bg-primary' : 'bg-info' }}">
                                {{ $seoRedirect->status_code }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Total hits</p>
                            <span class="fs-5 fw-bold">{{ number_format($seoRedirect->hits_count ?? 0) }}</span>
                        </div>
                        <div class="col-12 col-md-6" id="test-result-container" style="display:none">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Resultado del test</p>
                            <div id="test-result"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Hits en los ultimos 30 dias</h5>
                        <p class="mb-0 text-muted small" id="chart-subtitle">Cargando datos...</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reload-chart">
                        Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <div id="chart-container" style="min-height:280px">
                        <div class="d-flex align-items-center justify-content-center h-100 py-5" id="chart-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <canvas id="hits-chart" style="display:none"></canvas>
                        <div id="chart-table-fallback" style="display:none">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th class="text-end">Hits</th>
                                        </tr>
                                    </thead>
                                    <tbody id="chart-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
$(function () {

    var analyticsUrl = '{{ route('manager.seo.redirects.analytics', $seoRedirect) }}';
    var testUrl      = '{{ route('manager.seo.redirects.test', $seoRedirect) }}';
    var csrfToken    = $('meta[name="csrf-token"]').attr('content');
    var chartInstance = null;

    // ── Cargar datos del chart ──────────────────────────────────────────────
    function loadChartData() {
        $('#chart-loading').show();
        $('#hits-chart').hide();
        $('#chart-table-fallback').hide();

        $.getJSON(analyticsUrl, function (res) {
            var labels = res.labels || [];
            var data   = res.data   || [];
            var total  = res.total_30d || 0;

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
</script>
@endpush
