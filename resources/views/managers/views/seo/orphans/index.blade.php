@extends('layouts.managers')

@section('title', 'Contenido sin SEO')

@section('content')


    @php
        $total = array_sum($counts);
        $typeConfig = [
            'Course'      => ['label' => 'Cursos',         'subtitle' => 'Sin metadatos'],
            'Blog'        => ['label' => 'Blogs',          'subtitle' => 'Sin metadatos'],
            'Bundle'      => ['label' => 'Bundles',        'subtitle' => 'Sin metadatos'],
            'Instruction' => ['label' => 'Instrucciones',  'subtitle' => 'Sin metadatos'],
            'Certifier'   => ['label' => 'Certificadores', 'subtitle' => 'Sin metadatos'],
        ];
        $badgeConfig = [
            'Course'      => 'bg-primary',
            'Blog'        => 'bg-success',
            'Bundle'      => 'bg-warning text-dark',
            'Instruction' => 'bg-info',
            'Certifier'   => 'bg-secondary',
        ];
    @endphp

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Contenido sin SEO</h5>
                        <p class="mb-0 text-muted">Contenido publicado que no tiene metadatos SEO configurados</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" id="btn-generate-all" class="btn btn-primary" {{ $total === 0 ? 'disabled' : '' }}>
                            Generar todo
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total sin SEO</h6>
                                <h4 class="mb-1 fw-bold">{{ $total }}</h4>
                                <span class="text-muted">Sin metadatos</span>
                            </div>
                        </div>
                    </div>
                    @foreach($typeConfig as $typeKey => $cfg)
                        <div class="col-6 col-md-2">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">{{ $cfg['label'] }}</h6>
                                    <h4 class="mb-1 fw-bold">{{ $counts[$typeKey] ?? 0 }}</h4>
                                    <span class="text-muted">{{ $cfg['subtitle'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bulk toolbar --}}
            <div id="bulk-toolbar" class="d-none px-4 py-2 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <span class="small text-muted"><span id="bulk-count">0</span> seleccionados</span>
                    <button type="button" id="btn-bulk-generate" class="btn btn-sm btn-primary">
                        Generar seleccionados
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($total > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap" id="orphans-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>URL</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orphans as $item)
                                    @php
                                        $badge = $badgeConfig[$item['type']] ?? 'bg-secondary';
                                        $label = $typeConfig[$item['type']]['label'] ?? ucfirst($item['type']);
                                    @endphp
                                    <tr id="row-{{ $item['type'] }}-{{ $item['id'] }}">
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input bulk-checkbox"
                                                   value="{{ $item['id'] }}"
                                                   data-model-class="{{ $item['model_class'] }}"
                                                   data-model-id="{{ $item['id'] }}"
                                                   data-type="{{ $item['type'] }}">
                                        </td>
                                        <td>
                                            <span class="badge {{ $badge }} rounded-pill">{{ $label }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $item['title'] }}</div>
                                        </td>
                                        <td>
                                            @if(!empty($item['url']))
                                                <a href="{{ $item['url'] }}" target="_blank"
                                                   class="text-muted text-decoration-none"
                                                   title="{{ $item['url'] }}">
                                                    {{ $item['url'] }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item generate-btn" href="#"
                                                           data-model-class="{{ $item['model_class'] }}"
                                                           data-model-id="{{ $item['id'] }}"
                                                           data-type="{{ $item['type'] }}">
                                                            Generar metas
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-75"></i>
                        <h5 class="fw-bold mb-2">Todo el contenido tiene SEO configurado</h5>
                        <p class="text-muted">No hay contenido sin metadatos SEO</p>
                    </div>
                @endif
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script>
$(function () {

    var generateUrl = '{{ route('manager.seo.orphans.generate') }}';
    var bulkUrl     = '{{ route('manager.seo.orphans.bulk-generate') }}';
    var csrfToken   = $('meta[name="csrf-token"]').attr('content');

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // ── Helpers ─────────────────────────────────────────────────────────────

    function removeRow(type, id) {
        $('#row-' + type + '-' + id).fadeOut(300, function () {
            $(this).remove();
            if ($('#orphans-table tbody tr:visible').length === 0) {
                showEmptyState();
            }
        });
    }

    function showEmptyState() {
        $('#orphans-table').closest('.table-responsive').replaceWith(
            '<div class="text-center py-5">' +
            '<i class="fas fa-check-circle fa-3x mb-3 text-success opacity-75"></i>' +
            '<h5 class="fw-bold mb-2">Todo el contenido tiene SEO configurado</h5>' +
            '<p class="text-muted">No hay contenido sin metadatos SEO</p>' +
            '</div>'
        );
        $('#btn-generate-all').prop('disabled', true);
        $('#bulk-toolbar').addClass('d-none');
    }

    function updateBulkToolbar() {
        var checked = $('.bulk-checkbox:checked').length;
        var total   = $('.bulk-checkbox').length;
        $('#bulk-count').text(checked);
        checked > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked > 0 && checked === total);
    }

    // ── Seleccion masiva ─────────────────────────────────────────────────────

    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkToolbar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        updateBulkToolbar();
    });

    // ── Generar individual ───────────────────────────────────────────────────

    $(document).on('click', '.generate-btn', function (e) {
        e.preventDefault();
        var $link      = $(this);
        var modelClass = $link.data('model-class');
        var modelId    = $link.data('model-id');
        var type       = $link.data('type');

        $link.text('Generando...');

        $.ajax({
            url: generateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { model_class: modelClass, model_id: modelId },
            success: function (response) {
                if (response.status || response.success) {
                    toastr.success(response.message ?? 'SEO generado correctamente');
                    removeRow(type, modelId);
                } else {
                    toastr.error(response.message ?? 'No se pudo generar el SEO');
                    $link.text('Generar metas');
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al generar SEO');
                $link.text('Generar metas');
            }
        });
    });

    // ── Generar seleccionados ────────────────────────────────────────────────

    $('#btn-bulk-generate').on('click', function () {
        var $checked = $('.bulk-checkbox:checked');
        if (!$checked.length) {
            toastr.warning('Selecciona al menos un elemento.');
            return;
        }

        var $btn    = $(this).prop('disabled', true).text('Procesando...');
        var pending = $checked.length;
        var success = 0;

        $checked.each(function () {
            var modelClass = $(this).data('model-class');
            var modelId    = $(this).val();
            var type       = $(this).data('type');

            $.ajax({
                url: generateUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { model_class: modelClass, model_id: modelId },
                success: function (response) {
                    if (response.status || response.success) {
                        success++;
                        removeRow(type, modelId);
                    }
                },
                complete: function () {
                    pending--;
                    if (pending === 0) {
                        toastr.success(success + ' elemento(s) generados correctamente.');
                        $btn.prop('disabled', false).text('Generar seleccionados');
                        $('#bulk-toolbar').addClass('d-none');
                    }
                }
            });
        });
    });

    // ── Generar todo ─────────────────────────────────────────────────────────

    $('#btn-generate-all').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Generando...');

        $.ajax({
            url: bulkUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'SEO generado para todo el contenido.');
                showEmptyState();
                $btn.text('Completado');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al generar SEO masivo.');
                $btn.prop('disabled', false).text('Generar todo');
            }
        });
    });

});
</script>
@endpush
