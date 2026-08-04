@extends('layouts.managers')

@section('content')


    <div class="row g-3">

        {{-- Reglas de auto-confirmación --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold">Reglas de auto-confirmación</h6>
                            <p class="text-muted">
                                Cuando un correo de una empresa alcance la confianza mínima y todos los cursos tengan alias registrados,
                                se confirmará automáticamente sin revisión manual.
                            </p>
                        </div>
                        <a href="{{ route('manager.mails.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Volver al listado
                        </a>
                    </div>
                </div>

                {{-- Formulario nueva regla --}}
                <div class="card-body border-bottom bg-light">
                    <h6 class="fw-semibold mb-3 small text-muted">AGREGAR NUEVA REGLA</h6>
                    <div class="row g-2 align-items-end" id="new-rule-form">
                        <div class="col-md-5">
                            <label class="form-label form-label-sm mb-1">Empresa</label>
                            <select class="form-select" id="rule-enterprise" style="width:100%">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm mb-1">
                                Confianza mínima: <strong id="confidence-display">90%</strong>
                            </label>
                            <input type="range" class="form-range" id="rule-confidence"
                                   min="50" max="100" step="5" value="90">
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="save-rule-btn" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Agregar regla
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabla de reglas --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="rules-table">
                            <thead class="header-item">
                                <tr>
                                    <th>Empresa</th>
                                    <th class="text-center" style="width:150px">Confianza mínima</th>
                                    <th class="text-center" style="width:120px">Estado</th>
                                    <th class="text-center" style="width:100px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="rules-tbody">
                                @forelse($rules as $rule)
                                    <tr id="rule-row-{{ $rule->id }}">
                                        {{-- La empresa puede haberse borrado (soft delete) después de crear la regla --}}
                                        <td class="fw-semibold">{{ $rule->enterprise->title ?? 'Empresa eliminada' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-3 py-1 px-2">≥ {{ $rule->min_confidence }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block mb-0">
                                                <input class="form-check-input rule-toggle" type="checkbox"
                                                       data-id="{{ $rule->id }}"
                                                       {{ $rule->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown dropstart">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-vertical fs-5"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item delete-rule-btn" href="#"
                                                           data-id="{{ $rule->id }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No hay reglas configuradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // Select2 AJAX para empresa
    $('#rule-enterprise').select2({
        placeholder: 'Buscar empresa...',
        allowClear: true,
        ajax: {
            url: '{{ route("manager.mails.enterprises") }}',
            dataType: 'json', delay: 300,
            data: function (p) { return { q: p.term || '' }; },
            processResults: function (d) { return { results: d }; },
            cache: true
        }
    });

    // Slider de confianza
    $('#rule-confidence').on('input', function () {
        $('#confidence-display').text($(this).val() + '%');
    });

    // Guardar nueva regla
    $('#save-rule-btn').on('click', function () {
        var enterpriseId = $('#rule-enterprise').val();
        var minConfidence = $('#rule-confidence').val();
        if (!enterpriseId) { toastr.warning('Selecciona una empresa.'); return; }

        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: '{{ route("manager.settings.mails.rules.store") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ enterprise_id: enterpriseId, min_confidence: minConfidence }),
            success: function (r) {
                $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Agregar regla');
                if (!r.success) { toastr.error(r.message); return; }
                toastr.success(r.message);
                $('#empty-row').remove();
                var row = '<tr id="rule-row-' + r.rule.id + '">'
                    + '<td class="fw-semibold">' + $('<span>').text(r.rule.enterprise_title).html() + '</td>'
                    + '<td class="text-center"><span class="badge bg-primary rounded-3 py-1 px-2">≥ ' + r.rule.min_confidence + '%</span></td>'
                    + '<td class="text-center"><div class="form-check form-switch d-inline-block mb-0">'
                    + '<input class="form-check-input rule-toggle" type="checkbox" data-id="' + r.rule.id + '" checked></div></td>'
                    + '<td class="text-center"><div class="dropdown dropstart"><a href="#" class="text-muted" data-bs-toggle="dropdown">'
                    + '<i class="fas fa-ellipsis-vertical fs-5"></i></a><ul class="dropdown-menu">'
                    + '<li><a class="dropdown-item delete-rule-btn" href="#" data-id="' + r.rule.id + '">Eliminar</a></li>'
                    + '</ul></div></td></tr>';
                $('#rules-tbody').append(row);
                $('#rule-enterprise').val(null).trigger('change');
                $('#rule-confidence').val(90);
                $('#confidence-display').text('90%');
            },
            error: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Agregar regla');
                toastr.error('Error al guardar la regla.');
            }
        });
    });

    // Toggle activo/inactivo
    $(document).on('change', '.rule-toggle', function () {
        var id = $(this).data('id');
        var $cb = $(this);
        $.ajax({
            url: '{{ url("panel/settings/mails/rules") }}/' + id + '/toggle',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PATCH',
            },
            success: function (r) {
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (!r.success) $cb.prop('checked', !$cb.prop('checked'));
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
                $cb.prop('checked', !$cb.prop('checked'));
            }
        });
    });

    // Eliminar regla
    $(document).on('click', '.delete-rule-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!confirm('¿Eliminar esta regla?')) return;
        $.ajax({
            url: '{{ url("panel/settings/mails/rules") }}/' + id,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'DELETE',
            },
            success: function (r) {
                if (r.success) {
                    toastr.success(r.message);
                    $('#rule-row-' + id).remove();
                    if ($('#rules-tbody tr').length === 0) {
                        $('#rules-tbody').html('<tr id="empty-row"><td colspan="4" class="text-center py-4 text-muted">No hay reglas configuradas</td></tr>');
                    }
                } else { toastr.error(r.message); }
            },
            error: function () { toastr.error('Error al eliminar la regla.'); }
        });
    });

});
</script>
@endpush
