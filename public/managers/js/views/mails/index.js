$(function () {

    var $root      = $('#mails-index');
    var config     = $root.data('config') || {};
    var authUserId = config.authUserId || null;
    var csrfToken  = $('meta[name="csrf-token"]').attr('content');

    // ── Gráfica de actividad (DevExpress) ─────────────────────────────────────
    // DevExpress no está incluido en el proyecto (sin librería ni licencia):
    // llamar dxChart directamente revienta y corta el resto de este script
    // (daterange picker, filtros, selector de por página, auto-refresh...).
    if ($.fn.dxChart) {
        var chartData = config.chartData;
        $('#mails-activity-chart').dxChart({
            dataSource: chartData,
            commonSeriesSettings: { argumentField: 'date', type: 'bar' },
            series: [
                { valueField: 'received',  name: 'Recibidos',  color: '#adb5bd' },
                { valueField: 'processed', name: 'Procesados', color: '#28a745' },
                { valueField: 'failed',    name: 'Fallidos',   color: '#dc3545' },
                { valueField: 'pending',   name: 'Pendientes', color: '#ffc107' },
            ],
            argumentAxis: {
                label: {
                    customizeText: function (e) {
                        var d = new Date(e.value + 'T00:00:00');
                        return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
                    }
                }
            },
            valueAxis: { allowDecimals: false },
            legend: { verticalAlignment: 'bottom', horizontalAlignment: 'center', itemTextPosition: 'right' },
            tooltip: { enabled: true, shared: true },
            barGroupPadding: 0.2,
        });
    }

    // ── Daterange picker ───────────────────────────────────────────────────────
    if ($('#daterange').length) {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD', separator: ' - ',
                applyLabel: 'Aplicar', cancelLabel: 'Limpiar',
                daysOfWeek: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
                monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                firstDay: 1
            }
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#date_from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#date_to').val(picker.endDate.format('YYYY-MM-DD'));
        });
        $('#daterange').on('cancel.daterangepicker', function () {
            $(this).val('');
            $('#date_from').val('');
            $('#date_to').val('');
        });
    }

    // ── Select2 AJAX para empresa en filters modal ────────────────────────────
    $('#modalEnterprise').select2({
        dropdownParent: $('#filters-modal'),
        placeholder: 'Todas las empresas',
        allowClear: true,
        ajax: {
            url: config.routes.enterprises,
            dataType: 'json',
            delay: 300,
            data: function (params) { return { q: params.term || '' }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });

    // ── Aplicar filtros del modal ─────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterStatus').val($('#modalStatus').val());
        $('#filterEnterprise').val($('#modalEnterprise').val());
        $('#filterConfidence').val($('#modalConfidence').val());
        $('#filterAssignedTo').val($('#modalAssignedTo').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Per-page selector ─────────────────────────────────────────────────────
    $('#per-page-select').on('change', function () {
        var params = new URLSearchParams(window.location.search);
        params.set('per_page', $(this).val());
        params.delete('page');
        window.location.href = config.routes.index + '?' + params.toString();
    });

    // ── Dropdown overflow fix ─────────────────────────────────────────────────
    $(document).on('shown.bs.dropdown', function (e) {
        $(e.target).closest('.table-responsive').css('overflow', 'visible');
    });
    $(document).on('hidden.bs.dropdown', function (e) {
        $(e.target).closest('.table-responsive').css('overflow', '');
    });

    // ── Auto-refresh en pending_review ────────────────────────────────────────
    if (config.status === 'pending_review') {
        var getRowSlacks = function () {
            return $('#mails-tbody .mail-checkbox').map(function () { return $(this).val(); }).get();
        };
        setInterval(function () {
            var knownSlacks = getRowSlacks();
            $.ajax({
                url: location.href,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (html) {
                    $('#mails-tbody').html(html);
                    var newSlacks = getRowSlacks();
                    newSlacks.forEach(function (slack) {
                        if (knownSlacks.indexOf(slack) === -1) {
                            var $row = $('#mails-tbody').find('.mail-checkbox[value="' + slack + '"]').closest('tr');
                            $row.addClass('row-new-highlight');
                            setTimeout(function () { $row.removeClass('row-new-highlight'); }, 4000);
                        }
                    });
                }
            });
        }, 30000);
    }

    // ── Navegación por teclado (↑↓ + Enter) ──────────────────────────────────
    var $focusedRow = null;
    function focusRow($row) {
        if ($focusedRow) $focusedRow.removeClass('row-kb-focus');
        $focusedRow = $row;
        if ($focusedRow) {
            $focusedRow.addClass('row-kb-focus');
            $focusedRow[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
    $(document).on('keydown', function (e) {
        if ($(e.target).is('input, textarea, select')) return;
        var $rows = $('#mails-tbody tr.search-items');
        if (!$rows.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            var idx = $focusedRow ? $rows.index($focusedRow) : -1;
            focusRow($rows.eq(Math.min(idx + 1, $rows.length - 1)));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            var idx = $focusedRow ? $rows.index($focusedRow) : $rows.length;
            focusRow($rows.eq(Math.max(idx - 1, 0)));
        } else if (e.key === 'Enter' && $focusedRow) {
            e.preventDefault();
            var href = $focusedRow.find('a[href]').first().attr('href');
            if (href) window.location.href = href;
        }
    });

    // ── Confirmación rápida ───────────────────────────────────────────────────
    var qcSlack = null;
    $(document).on('click', '.quick-confirm-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        qcSlack = $(this).data('slack');
        $('#qc-enterprise-name').text($(this).data('enterprise'));
        $('#quick-confirm-modal').modal('show');
    });
    $('#quick-confirm-btn').on('click', function () {
        if (!qcSlack) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Procesando...');
        var url = config.routes.confirm.replace(':slack', qcSlack);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ quick_confirm: true }),
            success: function (r) {
                $('#quick-confirm-modal').modal('hide');
                $btn.prop('disabled', false).text('Confirmar y crear orden');
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 900);
            },
            error: function () {
                $('#quick-confirm-modal').modal('hide');
                $btn.prop('disabled', false).text('Confirmar y crear orden');
                toastr.error('Error al procesar la solicitud.');
            }
        });
    });

    // ── Acciones en lote ──────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'correo(s)',
        checkbox: '.mail-checkbox',
        selectAll: '#select-all-checkbox',
        idsParam: 'slacks',
        deleteActions: [],
    });

    // ── Alias faltantes ───────────────────────────────────────────────────────
    function renderAliasRow(type, value, count) {
        var safe = $('<span>').text(value).html();
        var badge = type === 'enterprise'
            ? '<span class="badge bg-danger">' + count + '</span>'
            : '<span class="badge bg-warning text-dark">' + count + '</span>';
        var assignBtn = '<button type="button" class="btn btn-outline-primary btn-sm assign-alias-btn" '
            + 'data-type="' + type + '" data-value="' + $('<span>').text(value).html() + '">'
            + 'Asignar</button>';
        if (type === 'enterprise') {
            return '<tr><td><code>' + safe + '</code></td><td class="text-center">' + badge + '</td><td class="text-center">' + assignBtn + '</td></tr>';
        }
        return '<tr><td>' + safe + '</td><td class="text-center">' + badge + '</td><td class="text-center">' + assignBtn + '</td></tr>';
    }
    function buildAliasesHtml(data) {
        var html = '';
        if (data.enterprise_codes.length > 0) {
            html += '<h6 class="fw-bold mb-2"><i class="fas fa-building me-1 text-warning"></i>Códigos de empresa sin alias <span class="text-muted fw-normal">(sin match en los últimos 30 días)</span></h6>';
            html += '<div class="table-responsive mb-4"><table class="table table-sm table-bordered"><thead><tr><th>Código recibido</th><th class="text-center mails-col-w80">Correos</th><th class="text-center mails-col-w110">Acción</th></tr></thead><tbody>';
            $.each(data.enterprise_codes, function (i, item) { html += renderAliasRow('enterprise', item.code, item.total); });
            html += '</tbody></table></div>';
        } else {
            html += '<div class="alert alert-success mb-4"><i class="fas fa-check-circle me-1"></i>Todos los códigos de empresa tienen alias registrado.</div>';
        }
        if (data.course_texts.length > 0) {
            html += '<h6 class="fw-bold mb-2"><i class="fas fa-book me-1 text-warning"></i>Textos de curso sin alias</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Texto recibido</th><th class="text-center mails-col-w80">Apariciones</th><th class="text-center mails-col-w110">Acción</th></tr></thead><tbody>';
            $.each(data.course_texts, function (i, item) { html += renderAliasRow('course', item.text, item.count); });
            html += '</tbody></table></div>';
        } else {
            html += '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>Todos los textos de curso tienen alias registrado.</div>';
        }
        if (data.enterprise_codes.length === 0 && data.course_texts.length === 0) {
            html = '<div class="alert alert-success text-center py-4"><i class="fas fa-circle-check fs-3 d-block mb-2"></i>No hay alias faltantes en los últimos 30 días.</div>';
        }
        return html;
    }
    $('#aliases-btn').on('click', function () {
        $('#aliases-modal-body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-primary fs-3"></i><p class="text-muted mt-2">Analizando correos...</p></div>');
        $('#aliases-modal').modal('show');
        $.ajax({
            url: config.routes.missingAliases,
            success: function (data) { $('#aliases-modal-body').html(buildAliasesHtml(data)); },
            error: function () { $('#aliases-modal-body').html('<div class="alert alert-danger">Error al cargar el reporte.</div>'); }
        });
    });

    // ── Asignar alias desde modal ─────────────────────────────────────────────
    $(document).on('click', '.assign-alias-btn', function () {
        var $btn = $(this);
        var type  = $btn.data('type');
        var value = $btn.data('value');
        var $row  = $btn.closest('tr');

        if ($row.next('.alias-form-row').length) { $row.next('.alias-form-row').remove(); return; }

        var isEnterprise = type === 'enterprise';
        var formHtml = '<tr class="alias-form-row bg-light"><td colspan="3" class="p-2">';
        formHtml += '<div class="d-flex gap-2 align-items-start flex-wrap">';
        if (isEnterprise) {
            formHtml += '<div class="flex-grow-1"><label class="form-label form-label-sm mb-1">Empresa destino</label>'
                + '<select class="form-select form-select-sm alias-enterprise-select mails-select-min-w200"><option></option></select></div>'
                + '<div><label class="form-label form-label-sm mb-1">Tipo de alias</label>'
                + '<select class="form-select form-select-sm alias-type-select">'
                + '<option value="TYPE_CODE">Código (ej: EMP01)</option>'
                + '<option value="TYPE_NAME">Nombre (ej: Empresa S.A.)</option>'
                + '</select></div>';
        } else {
            formHtml += '<div class="flex-grow-1"><label class="form-label form-label-sm mb-1">Curso destino</label>'
                + '<select class="form-select form-select-sm alias-course-select mails-select-min-w200"><option></option></select></div>';
        }
        formHtml += '<div class="d-flex flex-column justify-content-end mails-alias-btn-wrap">'
            + '<button type="button" class="btn btn-success btn-sm save-alias-btn" '
            + 'data-type="' + type + '" data-value="' + $('<span>').text(value).html() + '">'
            + 'Guardar</button></div>';
        formHtml += '</div></td></tr>';

        $row.after(formHtml);
        var $newRow = $row.next('.alias-form-row');

        if (isEnterprise) {
            $newRow.find('.alias-enterprise-select').select2({
                dropdownParent: $('#aliases-modal'),
                placeholder: 'Buscar empresa...',
                ajax: {
                    url: config.routes.enterprises,
                    dataType: 'json', delay: 300,
                    data: function (p) { return { q: p.term || '' }; },
                    processResults: function (d) { return { results: d }; },
                    cache: true
                }
            });
        } else {
            $newRow.find('.alias-course-select').select2({
                dropdownParent: $('#aliases-modal'),
                placeholder: 'Buscar curso...',
                ajax: {
                    url: config.routes.courses,
                    dataType: 'json', delay: 300,
                    data: function (p) { return { q: p.term || '' }; },
                    processResults: function (d) { return { results: d }; },
                    cache: true
                }
            });
        }
    });

    $(document).on('click', '.save-alias-btn', function () {
        var $btn  = $(this);
        var type  = $btn.data('type');
        var value = $btn.data('value');
        var $formRow = $btn.closest('.alias-form-row');
        var targetId, aliasType = null;

        if (type === 'enterprise') {
            targetId  = $formRow.find('.alias-enterprise-select').val();
            aliasType = $formRow.find('.alias-type-select').val();
            if (!targetId) { toastr.warning('Selecciona una empresa.'); return; }
        } else {
            targetId = $formRow.find('.alias-course-select').val();
            if (!targetId) { toastr.warning('Selecciona un curso.'); return; }
        }

        $btn.prop('disabled', true).text('Guardando...');
        $.ajax({
            url: config.routes.createAlias,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ type: type, value: value, target_id: targetId, alias_type: aliasType }),
            success: function (r) {
                if (r.success) {
                    toastr.success(r.message);
                    var $dataRow = $formRow.prev('tr');
                    $formRow.remove();
                    $dataRow.find('.assign-alias-btn').replaceWith('<span class="badge bg-success">Asignado</span>');
                } else {
                    toastr.error(r.message);
                    $btn.prop('disabled', false).text('Guardar');
                }
            },
            error: function () {
                toastr.error('Error al guardar el alias.');
                $btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    // ── Export CSV ────────────────────────────────────────────────────────────
    $('#export-btn').on('click', function (e) {
        e.preventDefault();
        var params = new URLSearchParams(window.location.search);
        params.delete('page');
        window.location.href = config.routes.export + '?' + params.toString();
    });

    // ── Preview modal ─────────────────────────────────────────────────────────
    $(document).on('click', '.preview-btn', function (e) {
        e.preventDefault();
        var slack      = $(this).data('slack');
        var openUrl    = config.routes.show.replace(':slack', slack);
        var previewUrl = config.routes.preview.replace(':slack', slack);

        $('#preview-from').text('');
        $('#preview-open-link').attr('href', openUrl);
        $('#preview-modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin text-primary fs-3"></i><p class="text-muted mt-2">Cargando correo...</p></div>');
        $('#preview-modal').modal('show');

        $.ajax({
            url: previewUrl,
            success: function (r) {
                if (!r.success) {
                    $('#preview-modal-body').html('<div class="alert alert-danger m-3">' + $('<span>').text(r.message || 'Error al cargar.').html() + '</div>');
                    return;
                }
                $('#preview-from').text(r.from + ' — ' + r.received_at);

                var statusMap = {
                    pending_review: ['bg-light-warning text-warning', 'Pendiente'],
                    processed:      ['bg-light-success text-success', 'Procesado'],
                    failed:         ['bg-light-danger text-danger',   'Fallido'],
                    ignored:        ['bg-light-secondary text-secondary', 'Ignorado']
                };
                var sm   = statusMap[r.status] || ['bg-light-secondary text-secondary', r.status];
                var body = '<div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">';
                body += '<span class="badge ' + sm[0] + ' rounded-3 py-1 px-2">' + sm[1] + '</span>';
                if (r.enterprise) {
                    body += '<span class="badge bg-light-primary text-primary rounded-3 py-1 px-2"><i class="fas fa-building me-1"></i>' + $('<span>').text(r.enterprise).html() + '</span>';
                }
                if (r.confidence_score !== null) {
                    var cs  = r.confidence_score;
                    var csc = cs >= 90 ? 'bg-success' : (cs >= 50 ? 'bg-warning' : 'bg-danger');
                    body += '<span class="badge ' + csc + ' rounded-3 py-1 px-2">' + cs + '%</span>';
                }
                body += '</div>';
                body += '<div class="p-3"><h6 class="fw-semibold mb-1">' + $('<span>').text(r.subject).html() + '</h6>';
                body += '<pre class="bg-light rounded p-3 mails-preview-pre mails-preview-pre-lg">' + $('<span>').text(r.raw_body || '').html() + '</pre></div>';
                if (r.enterprise_stats) {
                    var es        = r.enterprise_stats;
                    var rateClass = es.success_rate >= 80 ? 'text-success' : (es.success_rate >= 50 ? 'text-warning' : 'text-danger');
                    body += '<div class="p-3 border-top bg-light"><h6 class="fw-semibold text-muted mb-2">HISTORIAL DE LA EMPRESA</h6>';
                    body += '<div class="d-flex flex-wrap gap-3">';
                    body += '<span><strong>' + es.total + '</strong> correos totales</span>';
                    body += '<span class="text-success"><strong>' + es.processed + '</strong> procesados</span>';
                    body += '<span class="text-danger"><strong>' + es.failed + '</strong> fallidos</span>';
                    body += '<span class="' + rateClass + '"><strong>' + es.success_rate + '%</strong> tasa de éxito</span>';
                    body += '</div></div>';
                }
                if (r.error_log) {
                    body += '<div class="p-3 border-top"><h6 class="fw-semibold text-danger mb-2"><i class="fas fa-triangle-exclamation me-1"></i>Error log</h6>';
                    body += '<pre class="bg-light rounded p-3 text-danger mails-preview-pre mails-preview-pre-sm">' + $('<span>').text(r.error_log).html() + '</pre></div>';
                }
                $('#preview-modal-body').html(body);
            },
            error: function () {
                $('#preview-modal-body').html('<div class="alert alert-danger m-3">Error al cargar la vista previa.</div>');
            }
        });
    });

    // ── Descartar ─────────────────────────────────────────────────────────────
    var currentSlack = null;
    $(document).on('click', '.discard-btn', function (e) {
        e.preventDefault();
        currentSlack = $(this).data('slack');
        $('#discard-modal').modal('show');
    });
    $('#discard-confirm-btn').on('click', function () {
        if (!currentSlack) return;
        var url = config.routes.discard.replace(':slack', currentSlack);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (r) {
                $('#discard-modal').modal('hide');
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 900);
            },
            error: function () {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.');
            }
        });
    });

    // ── Asignarme desde índice ────────────────────────────────────────────────
    $(document).on('click', '.assign-me-btn', function (e) {
        e.preventDefault();
        var $btn  = $(this);
        var slack = $btn.data('slack');
        var url   = config.routes.assign.replace(':slack', slack);
        $btn.prop('disabled', true);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ user_id: authUserId }),
            success: function (r) {
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 700);
                else $btn.prop('disabled', false);
            },
            error: function () {
                toastr.error('Error al asignar el correo.');
                $btn.prop('disabled', false);
            }
        });
    });

    // ── Eliminar individual vía modal ─────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
