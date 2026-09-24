{{-- invoice · diseño A — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $distributor = $invoice->distributor;
@endphp
    <article class="doc-a-sheet">

        <div class="doc-a-stamp is-{{ $tone }}" aria-label="Estado: {{ $invoice->condition?->title }}">
            <strong>{{ $invoice->condition?->title ?? 'Sin estado' }}</strong>
            @if($tone === 'paid' && $invoice->payment_at)
                <span>{{ $F::date($invoice->payment_at, 'j M Y') }}</span>
            @endif
        </div>

        <header class="doc-a-head">
            <div>
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-a-logo">
                <div class="doc-a-kind">Factura a distribuidor</div>
            </div>
            <div class="doc-a-number"><small>Factura</small>{{ $invoice->reference }}</div>
        </header>

        <dl class="doc-a-facts">
            <div>
                <dt>Emitida</dt>
                <dd>{{ $F::date($invoice->created_at) }}</dd>
            </div>
            <div>
                <dt>Periodo facturado</dt>
                <dd>{{ $invoice->from_at ? $F::date($invoice->from_at, 'j M') . ' – ' . $F::date($invoice->to_at, 'j M Y') : '—' }}</dd>
            </div>
            <div>
                <dt>Forma de pago</dt>
                <dd>{{ $invoice->method?->title ?? '—' }}</dd>
            </div>
            <div>
                <dt>Pagada</dt>
                <dd>{{ $invoice->payment_at ? $F::date($invoice->payment_at) : 'Pendiente' }}</dd>
            </div>
        </dl>

        <section class="doc-a-parties">
            <div>
                <div class="doc-a-party-label">Facturar a</div>
                <div class="doc-a-party-name">{{ $distributor->title ?? 'Distribuidor no disponible' }}</div>
                @if($distributor?->nit)<div class="doc-a-party-line">NIT {{ $distributor->nit }}</div>@endif
                @if($distributor?->address)<div class="doc-a-party-line">{{ $distributor->address }}</div>@endif
                @if($distributor?->email)<div class="doc-a-party-line">{{ $distributor->email }}</div>@endif
            </div>
            @if($invoice->notes)
                <div>
                    <div class="doc-a-party-label">Notas</div>
                    <div class="doc-a-party-line">{{ $invoice->notes }}</div>
                </div>
            @endif
        </section>

        <table class="doc-a-items">
            <thead>
                <tr>
                    <th class="doc-a-line">N.º</th>
                    <th>Curso</th>
                    <th class="doc-a-qty">Cant.</th>
                    <th class="doc-a-amount">Valor unitario</th>
                    <th class="doc-a-amount">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="doc-a-line doc-num">{{ $loop->iteration }}</td>
                        <td><div class="doc-a-item-title">{{ $item->course->title ?? 'Curso no disponible' }}</div></td>
                        <td class="doc-a-qty doc-num">{{ ceil($item->quantity) }}</td>
                        <td class="doc-a-amount doc-num">{{ $F::money($item->subtotal) }}</td>
                        <td class="doc-a-amount doc-num">{{ $F::money($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="doc-a-totals">
            <div class="doc-a-totals-row"><span>Base</span><span class="doc-num">{{ $F::money($invoice->total_after_discount) }}</span></div>
            @if($invoice->total_discount_amount > 0)
                <div class="doc-a-totals-row"><span>Descuentos</span><span class="doc-num">− {{ $F::money($invoice->total_discount_amount) }}</span></div>
            @endif
            @if($invoice->total_tax_amount > 0)
                <div class="doc-a-totals-row"><span>IVA</span><span class="doc-num">{{ $F::money($invoice->total_tax_amount) }}</span></div>
            @endif
            <div class="doc-a-totals-row is-grand"><span>Total</span><span class="doc-num">{{ $F::money($invoice->total_invoices_amount) }}</span></div>
        </div>

        <footer class="doc-a-foot">
@if(! empty($links['details']))
            <a href="{{ $links['details'] }}">Ver el reparto por empresa</a>
@endif
        </footer>

    </article>
