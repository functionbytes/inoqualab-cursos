{{--
    Barra flotante + modal de acción en lote, para usar junto con
    public/managers/js/bulk-actions.js.

    Uso en la vista index:

        @include('managers.includes.bulk-toolbar-modal', [
            'bulkEntityLabel' => 'curso(s)',
            'bulkActions' => [
                ['value' => 'publish', 'label' => 'Publicar'],
                ['value' => 'hide', 'label' => 'Ocultar'],
                ['value' => 'delete', 'label' => 'Eliminar'],
            ],
        ])

    y en la tabla: <th><input type="checkbox" id="select-all"></th> en el
    thead, <td><input type="checkbox" class="bulk-checkbox" value="{{ $item->id }}"></td>
    en cada fila; luego en @push('scripts'):

        BulkActions.init({
            url: '{{ route('manager.courses.bulk-action') }}',
            entityLabel: 'curso(s)',
        });
--}}

<div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
    <button type="button" class="btn btn-primary shadow-lg px-4"
            data-bs-toggle="modal" data-bs-target="#bulk-modal">
        <span data-bulk-count>0</span> seleccionado(s) &mdash; Aplicar acción
    </button>
</div>

<div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acción masiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Se aplicará la acción sobre <strong><span data-bulk-count>0</span> {{ $bulkEntityLabel }}</strong>.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Acción</label>
                    <select id="bulk-action-select" class="form-select">
                        <option value="">Seleccionar acción...</option>
                        @foreach($bulkActions as $bulkAction)
                            <option value="{{ $bulkAction['value'] }}">{{ $bulkAction['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer flex-column">
                <button id="btn-bulk-apply" type="button" class="btn btn-primary w-100 mb-2">
                    Aplicar
                </button>
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/bulk-toolbar-modal.css') }}">
@endpush
