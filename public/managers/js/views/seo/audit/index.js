$(function () {

    var $config = $('#audit-config');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var gradeMap = { A: 'success', B: 'primary', C: 'warning', D: 'danger', F: 'danger' };
    var bulkTimer = null;
    var brokenTimer = null;

    // ── helpers ───────────────────────────────────────────────────────────────
    function gradeClass(grade) {
        return gradeMap[grade] ?? 'secondary';
    }

    function scoreToGrade(score) {
        if (score >= 90) return 'A';
        if (score >= 75) return 'B';
        if (score >= 60) return 'C';
        if (score >= 40) return 'D';
        return 'F';
    }

    function renderGradeBadge(grade) {
        var cls = gradeClass(grade);
        return '<span class="badge bg-' + cls + ' px-2">' + grade + '</span>';
    }

    // ── Auditoría por URL ─────────────────────────────────────────────────────
    $('#btn-audit-url').on('click', function () {
        var url = $('#audit-url-input').val().trim();
        var $btn = $(this);
        var $input = $('#audit-url-input');

        $input.removeClass('is-invalid');

        if (!url) {
            $input.addClass('is-invalid');
            $input.next('.invalid-feedback').text('Ingresa una URL válida.');
            return;
        }

        $btn.prop('disabled', true).text('Auditando...');
        $('#url-audit-result').addClass('d-none');

        $.ajax({
            url: $config.data('audit-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { url: url },
            success: function (res) {
                renderUrlResult(res.data);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $input.addClass('is-invalid')
                          .next('.invalid-feedback').text(xhr.responseJSON.errors?.url?.[0] ?? 'URL inválida.');
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al auditar la URL.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Auditar URL');
            }
        });
    });

    function renderUrlResult(data) {
        var grade = scoreToGrade(data.score);
        var cls = gradeClass(grade);
        var $circle = $('#url-score-circle');

        $circle.removeClass('grade-a grade-b grade-c grade-d grade-f')
               .addClass('grade-' + grade.toLowerCase());
        $('#url-score-value').text(data.score);
        $('#url-grade-badge').removeClass().addClass('badge fs-6 px-3 py-2 bg-' + cls)
                             .text('Grade ' + grade);

        renderCheckList('#url-issues-list', data.issues, 'danger');
        renderCheckList('#url-passed-list', data.passed, 'success');

        $('#url-issues-count').text(data.issues.length);
        $('#url-passed-count').text(data.passed.length);
        $('#url-audit-result').removeClass('d-none');
    }

    // ── PageSpeed Insights ────────────────────────────────────────────────────
    $('#btn-pagespeed').on('click', function () {
        var url = $('#pagespeed-url-input').val().trim();
        var strategy = $('#pagespeed-strategy').val();
        var $btn = $(this);

        if (!url) {
            toastr.error('Ingresa una URL válida.');
            return;
        }

        $btn.prop('disabled', true).text('Analizando...');
        $('#pagespeed-result').addClass('d-none');

        $.ajax({
            url: $config.data('core-web-vitals-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { url: url, strategy: strategy },
            success: function (res) {
                $('#ps-performance').text(res.performance_score);
                $('#ps-seo').text(res.seo_score);
                $('#ps-accessibility').text(res.accessibility_score);
                $('#ps-best-practices').text(res.best_practices_score);
                $('#ps-lcp').text(res.lcp);
                $('#ps-cls').text(res.cls);
                $('#ps-fcp').text(res.fcp);
                $('#ps-ttfb').text(res.ttfb);
                $('#ps-fid').text(res.fid);
                $('#pagespeed-result').removeClass('d-none');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.error ?? xhr.responseJSON?.message ?? 'Error al analizar la URL.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Analizar');
            }
        });
    });

    function renderCheckList(selector, items, type) {
        var $list = $(selector).empty();
        if (!items || !items.length) {
            $list.append('<li class="list-group-item text-muted small px-0">Ninguno</li>');
            return;
        }
        items.forEach(function (item) {
            var message = (item && typeof item === 'object') ? (item.message ?? '') : item;
            $list.append(
                '<li class="list-group-item px-0 py-1 border-0 border-bottom">' +
                '<i class="fas fa-circle-' + (type === 'danger' ? 'xmark text-danger' : 'check text-success') + ' me-2 small"></i>' +
                '<span class="small">' + $('<div>').text(message).html() + '</span>' +
                '</li>'
            );
        });
    }

    // ── Auditoría masiva — iniciar job ────────────────────────────────────────
    $('#btn-bulk-start').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Iniciando...');

        $.ajax({
            url: $config.data('bulk-start-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                toastr.info('Auditoría masiva iniciada.');
                $('#bulk-progress-section').removeClass('d-none');
                $('#bulk-summary-section, #bulk-empty').addClass('d-none');
                $('#bulk-results-section').hide();
                pollBulkProgress();
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al iniciar la auditoría.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Auditar todas las metas');
            }
        });
    });

    function pollBulkProgress() {
        clearInterval(bulkTimer);
        bulkTimer = setInterval(function () {
            $.getJSON($config.data('bulk-progress-url'), function (res) {
                var pct = res.total > 0 ? Math.round((res.processed / res.total) * 100) : 0;
                $('#bulk-progress-bar').css('width', pct + '%');
                $('#bulk-progress-text').text(res.processed + ' / ' + res.total);

                if (res.status === 'done' || res.processed >= res.total) {
                    clearInterval(bulkTimer);
                    $('#bulk-progress-section').addClass('d-none');
                    toastr.success('Auditoría masiva completada.');
                    loadAllAuditResults();
                }
            });
        }, 2000);
    }

    // ── Ver últimos resultados de auditoría masiva ────────────────────────────
    $('#btn-load-all-audits').on('click', function () {
        loadAllAuditResults();
    });

    function loadAllAuditResults() {
        $.getJSON($config.data('bulk-all-url'), function (res) {
            if (!res.data || !res.data.length) {
                $('#bulk-empty').removeClass('d-none');
                $('#bulk-results-section').hide();
                $('#bulk-summary-section').addClass('d-none');
                return;
            }

            var s = res.summary;
            $('#bulk-stat-total').text(s.total ?? 0);
            $('#bulk-stat-avg').text(s.avg_score ?? '—');
            $('#bulk-stat-issues').text(s.with_issues ?? 0);
            $('#bulk-stat-a').text(s.score_a ?? 0);
            $('#bulk-summary-section').removeClass('d-none');

            var metaEditUrlTemplate = $config.data('meta-edit-url-template');
            var $tbody = $('#bulk-results-tbody').empty();
            res.data.forEach(function (row) {
                var grade = scoreToGrade(row.score);
                var cls = gradeClass(grade);
                $tbody.append(
                    '<tr>' +
                    '<td><code class="small">' + $('<div>').text(row.url ?? row.title ?? '—').html() + '</code></td>' +
                    '<td class="text-center"><strong>' + row.score + '</strong></td>' +
                    '<td class="text-center">' + renderGradeBadge(grade) + '</td>' +
                    '<td class="text-center"><span class="badge bg-' + (row.issues_count > 0 ? 'danger' : 'light text-dark border') + '">' + (row.issues_count ?? 0) + '</span></td>' +
                    '<td class="text-center"><span class="badge bg-success">' + (row.passed_count ?? 0) + '</span></td>' +
                    '<td class="text-center">' +
                    (row.meta_id ? '<a href="' + metaEditUrlTemplate.replace(':id', row.meta_id) + '" class="btn btn-sm btn-light">Ver meta</a>' : '—') +
                    '</td>' +
                    '</tr>'
                );
            });

            $('#bulk-results-section').show();
            $('#bulk-empty').addClass('d-none');
        }).fail(function () {
            toastr.error('Error al cargar los resultados.');
        });
    }

    // ── Verificar canonicals ──────────────────────────────────────────────────
    $('#btn-check-canonicals').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Verificando...');

        $('#canonical-progress-section').removeClass('d-none');
        $('#canonical-summary, #canonical-empty').addClass('d-none');
        $('#canonical-results-section').hide();

        $.getJSON($config.data('check-canonicals-url'), function (res) {
            $('#canonical-progress-section').addClass('d-none');

            var s = res.summary;
            $('#canonical-stat-total').text(s.total ?? 0);
            $('#canonical-stat-ok').text(s.ok ?? 0);
            $('#canonical-stat-broken').text(s.broken ?? 0);
            $('#canonical-summary').removeClass('d-none');

            if (!res.results || !res.results.length) {
                $('#canonical-empty').removeClass('d-none');
            } else {
                var $tbody = $('#canonical-results-tbody').empty();
                res.results.forEach(function (row) {
                    var badge = row.ok
                        ? '<span class="badge bg-success">' + row.status + '</span>'
                        : '<span class="badge bg-danger">' + (row.status || 'Error') + '</span>';
                    $tbody.append(
                        '<tr>' +
                        '<td><small class="fw-semibold">' + $('<div>').text(row.title ?? '—').html() + '</small></td>' +
                        '<td><code class="small">' + $('<div>').text(row.canonical_url ?? '—').html() + '</code></td>' +
                        '<td class="text-center">' + badge + '</td>' +
                        '</tr>'
                    );
                });
                $('#canonical-results-section').show();
            }
        }).fail(function () {
            toastr.error('Error al verificar los canonicals.');
            $('#canonical-progress-section').addClass('d-none');
        }).always(function () {
            $btn.prop('disabled', false).text('Verificar canonicals');
        });
    });

    // ── Links rotos ───────────────────────────────────────────────────────────
    $('#btn-broken-links-start').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Iniciando...');

        $.ajax({
            url: $config.data('broken-links-start-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                toastr.info('Verificación de links iniciada.');
                $('#broken-progress-section').removeClass('d-none');
                $('#broken-empty').addClass('d-none');
                $('#broken-results-section').hide();
                pollBrokenProgress();
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al iniciar la verificación.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Verificar links rotos');
            }
        });
    });

    function pollBrokenProgress() {
        clearInterval(brokenTimer);
        brokenTimer = setInterval(function () {
            $.getJSON($config.data('broken-links-progress-url'), function (res) {
                var pct = res.total > 0 ? Math.round((res.checked / res.total) * 100) : 0;
                $('#broken-progress-bar').css('width', pct + '%');
                $('#broken-progress-text').text(res.checked + ' / ' + res.total);

                if (res.status === 'done' || res.checked >= res.total) {
                    clearInterval(brokenTimer);
                    $('#broken-progress-section').addClass('d-none');

                    if (!res.broken || !res.broken.length) {
                        $('#broken-empty').removeClass('d-none');
                        toastr.success('No se encontraron links rotos.');
                    } else {
                        var $list = $('#broken-links-list').empty();
                        res.broken.forEach(function (link) {
                            $list.append(
                                '<li class="list-group-item d-flex align-items-center gap-3">' +
                                '<span class="badge bg-danger flex-shrink-0">' + (link.status || 'Error') + '</span>' +
                                '<div class="flex-grow-1 min-w-0">' +
                                '<code class="small text-break">' + $('<div>').text(link.url ?? '').html() + '</code>' +
                                (link.source ? '<div class="text-muted broken-link-source">Desde: ' + $('<div>').text(link.source).html() + '</div>' : '') +
                                '</div>' +
                                '</li>'
                            );
                        });
                        $('#broken-results-section').show();
                        toastr.warning(res.broken.length + ' link(s) roto(s) encontrado(s).');
                    }
                }
            });
        }, 2000);
    }

    // ── Links internos ────────────────────────────────────────────────────────
    $('#btn-internal-links').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Analizando...');

        $('#internal-progress-section').removeClass('d-none');
        $('#internal-empty').addClass('d-none');
        $('#internal-results-section').hide();

        $.getJSON($config.data('internal-links-url'), function (res) {
            $('#internal-progress-section').addClass('d-none');

            $('#internal-stat-scanned').text(res.scanned ?? 0);
            $('#internal-stat-total').text(res.total_urls ?? 0);
            $('#internal-stat-orphans').text(res.orphans ? res.orphans.length : 0);

            var $orphansList = $('#orphans-list').empty();
            if (res.orphans && res.orphans.length) {
                res.orphans.forEach(function (url) {
                    $orphansList.append(
                        '<li class="list-group-item px-0 py-1 border-0 border-bottom">' +
                        '<i class="fas fa-unlink text-warning me-2 small"></i>' +
                        '<code class="small">' + $('<div>').text(url).html() + '</code>' +
                        '</li>'
                    );
                });
            } else {
                $orphansList.append('<li class="list-group-item text-muted small px-0">No hay páginas huérfanas</li>');
            }

            var $inboundList = $('#inbound-list').empty();
            var inbound = res.inbound_counts ?? {};
            var sorted = Object.entries(inbound).sort(function (a, b) { return b[1] - a[1]; }).slice(0, 10);
            if (sorted.length) {
                sorted.forEach(function (entry) {
                    $inboundList.append(
                        '<li class="list-group-item px-0 py-1 border-0 border-bottom d-flex align-items-center gap-2">' +
                        '<span class="badge bg-primary-subtle text-primary flex-shrink-0">' + entry[1] + '</span>' +
                        '<code class="small text-truncate">' + $('<div>').text(entry[0]).html() + '</code>' +
                        '</li>'
                    );
                });
            } else {
                $inboundList.append('<li class="list-group-item text-muted small px-0">Sin datos de enlaces entrantes</li>');
            }

            $('#internal-results-section').show();
        }).fail(function () {
            toastr.error('Error al analizar los links internos.');
            $('#internal-progress-section').addClass('d-none');
            $('#internal-empty').removeClass('d-none');
        }).always(function () {
            $btn.prop('disabled', false).text('Analizar links internos');
        });
    });

});
