{{-- invoice · diseño C — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $distributor = $invoice->distributor;
@endphp
    <div class="doc-c">

        <div class="doc-c-band">
            <div class="doc-c-band-id">
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-c-logo">
                <span class="doc-c-band-rule" aria-hidden="true"></span>
                <div>
                <div class="doc-c-band-kind">Factura a distribuidor</div>
                <div class="doc-c-band-code">{{ $invoice->reference }}</div>
                </div>
            </div>
            <span class="doc-c-state is-{{ $tone }}">{{ $invoice->condition?->title ?? 'Sin estado' }}</span>
        </div>

        <div class="doc-c-grid">
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Distribuidor</div>
                <div class="doc-c-cell-value">{{ $distributor->title ?? '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">NIT</div>
                <div class="doc-c-cell-value">{{ $distributor?->nit ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Correo</div>
                <div class="doc-c-cell-value">{{ $distributor?->email ?: '—' }}</div>
            </div>
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Dirección</div>
                <div class="doc-c-cell-value">{{ $distributor?->address ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Forma de pago</div>
                <div class="doc-c-cell-value">{{ $invoice->method?->title ?? '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Número interno</div>
                <div class="doc-c-cell-value">{{ $invoice->number ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Emitida</div>
                <div class="doc-c-cell-value">{{ $F::date($invoice->created_at, 'd/m/Y') }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Periodo desde</div>
                <div class="doc-c-cell-value">{{ $invoice->from_at ? $F::date($invoice->from_at, 'd/m/Y') : '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Periodo hasta</div>
                <div class="doc-c-cell-value">{{ $invoice->to_at ? $F::date($invoice->to_at, 'd/m/Y') : '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Pagada</div>
                <div class="doc-c-cell-value">{{ $invoice->payment_at ? $F::date($invoice->payment_at, 'd/m/Y') : '—' }}</div>
            </div>
        </div>

        <div class="doc-c-section d-flex justify-content-between align-items-center gap-3">
            <span>Cursos facturados</span>
@if(! empty($links['details']))
            <a href="{{ $links['details'] }}" class="fw-semibold">Ver reparto por empresa</a>
@endif
        </div>
        <div class="doc-c-table-wrap">
            <table class="doc-c-table">
                <thead>
                    <tr>
                        <th class="doc-c-line">N.º</th>
                        <th>Curso</th>
                        <th class="is-center">Cant.</th>
                        <th class="is-end">Valor unitario</th>
                        <th class="is-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="doc-c-line doc-num">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $item->course->title ?? 'Curso no disponible' }}</td>
                            <td class="is-center doc-num">{{ ceil($item->quantity) }}</td>
                            <td class="is-end doc-num">{{ $F::money($item->subtotal) }}</td>
                            <td class="is-end doc-num">{{ $F::money($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="doc-c-sum">
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Base</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($invoice->total_after_discount) }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Descuentos</div>
                <div class="doc-c-cell-value doc-num">{{ $invoice->total_discount_amount > 0 ? '− ' . $F::money($invoice->total_discount_amount) : $F::money(0) }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">IVA</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($invoice->total_tax_amount) }}</div>
            </div>
            <div class="doc-c-cell is-total">
                <div class="doc-c-cell-label">Total</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($invoice->total_invoices_amount) }}</div>
            </div>
        </div>

    </div>
