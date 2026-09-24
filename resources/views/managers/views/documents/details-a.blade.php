{{-- details · diseño A — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $groups = $F::enterpriseGroups($details);
    $detailTotal = $groups->sum('total');
    $seats = $groups->sum('seats');
@endphp
    <article class="doc-a-sheet">

        <div class="doc-a-stamp is-{{ $tone }}" aria-label="Estado: {{ $invoice->condition?->title }}">
            <strong>{{ $invoice->condition?->title ?? 'Sin estado' }}</strong>
        </div>

        <header class="doc-a-head">
            <div>
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-a-logo">
                <div class="doc-a-kind">Reparto por empresa</div>
            </div>
            <div class="doc-a-number"><small>Factura</small>{{ $invoice->reference }}</div>
        </header>

        <dl class="doc-a-facts">
            <div>
                <dt>Distribuidor</dt>
                <dd>{{ $invoice->distributor->title ?? '—' }}</dd>
            </div>
            <div>
                <dt>Periodo facturado</dt>
                <dd>{{ $invoice->from_at ? $F::date($invoice->from_at, 'j M') . ' – ' . $F::date($invoice->to_at, 'j M Y') : '—' }}</dd>
            </div>
            <div>
                <dt>Empresas</dt>
                <dd class="doc-num">{{ $groups->count() }}</dd>
            </div>
            <div>
                <dt>Inscripciones</dt>
                <dd class="doc-num">{{ number_format($seats, 0, ',', '.') }}</dd>
            </div>
        </dl>

        @forelse($groups as $group)
            <section class="doc-a-group">
                <div class="doc-a-group-head">
                    <h3>{{ $group['name'] }}</h3>
                    <span class="doc-num">{{ number_format($group['seats'], 0, ',', '.') }} inscripciones</span>
                </div>
                <table class="doc-a-items">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th class="doc-a-qty">Cant.</th>
                            <th class="doc-a-amount">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['courses'] as $course)
                            <tr>
                                <td>{{ $course['course'] }}</td>
                                <td class="doc-a-qty doc-num">{{ ceil($course['quantity']) }}</td>
                                <td class="doc-a-amount doc-num">{{ $F::money($course['totalAmount']) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="fw-bold">Subtotal de {{ $group['name'] }}</td>
                            <td class="doc-a-amount doc-num fw-bold">{{ $F::money($group['total']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        @empty
            <p class="text-muted text-center py-5 mb-0">
                Esta factura no tiene inscripciones asociadas a empresas, así que no hay reparto que mostrar.
            </p>
        @endforelse

        <div class="doc-a-totals">
            <div class="doc-a-totals-row"><span>Suma del reparto</span><span class="doc-num">{{ $F::money($detailTotal) }}</span></div>
            @if($invoice->total_tax_amount > 0)
                <div class="doc-a-totals-row"><span>IVA de la factura</span><span class="doc-num">{{ $F::money($invoice->total_tax_amount) }}</span></div>
            @endif
            <div class="doc-a-totals-row is-grand"><span>Total factura</span><span class="doc-num">{{ $F::money($invoice->total_invoices_amount) }}</span></div>
        </div>

        <footer class="doc-a-foot">
@if(! empty($links['view']))
            <a href="{{ $links['view'] }}">Volver a la factura</a>
@endif
        </footer>

    </article>
