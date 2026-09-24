{{-- order · diseño A — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($order->condition_id);
    $user = $order->user;
    $activity = $order->activity;
@endphp
    <article class="doc-a-sheet">

        <div class="doc-a-stamp is-{{ $tone }}" aria-label="Estado: {{ $order->condition?->title }}">
            <strong>{{ $order->condition?->title ?? 'Sin estado' }}</strong>
            @if($tone === 'paid' && $order->payment_at)
                <span>{{ $F::date($order->payment_at, 'j M Y') }}</span>
            @endif
        </div>

        <header class="doc-a-head">
            <div>
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-a-logo">
                <div class="doc-a-kind">Orden de compra</div>
            </div>
            <div class="doc-a-number"><small>Número de orden</small>{{ $order->slack }}</div>
        </header>

        <dl class="doc-a-facts">
            <div>
                <dt>Referencia</dt>
                <dd>{{ $order->reference ?: '—' }}</dd>
            </div>
            <div>
                <dt>Emitida</dt>
                <dd>{{ $F::date($order->created_at) }}</dd>
            </div>
            <div>
                <dt>Forma de pago</dt>
                <dd>{{ $order->method?->title ?? '—' }} <span class="fw-normal text-muted">({{ Str::lower($order->type?->title ?? '') }})</span></dd>
            </div>
            <div>
                <dt>Cupón</dt>
                <dd>{{ $order->coupon?->code ?? 'Sin cupón' }}</dd>
            </div>
        </dl>

        <section class="doc-a-parties">
            <div>
                <div class="doc-a-party-label">Cliente</div>
                <div class="doc-a-party-name">{{ $user ? trim($user->firstname . ' ' . $user->lastname) : 'Usuario eliminado' }}</div>
                @if($user)
                    <div class="doc-a-party-line">Documento {{ $user->identification ?: '—' }}</div>
                    <div class="doc-a-party-line">{{ $user->email }}</div>
                    @if($user->cellphone)<div class="doc-a-party-line">{{ $user->cellphone }}</div>@endif
                    @if($user->address)<div class="doc-a-party-line">{{ $user->address }}</div>@endif
                @endif
            </div>
            @if($activity)
                <div>
                    <div class="doc-a-party-label">Vendida por cuenta de</div>
                    <div class="doc-a-party-name">{{ $activity->distributor->title ?? 'Distribuidor no disponible' }}</div>
                    @if($activity->distributor->nit ?? null)<div class="doc-a-party-line">NIT {{ $activity->distributor->nit }}</div>@endif
                    <div class="doc-a-party-line">Empresa: {{ $activity->enterprise->title ?? '—' }}</div>
                    <div class="doc-a-party-line">Encargado: {{ trim(($activity->staff->firstname ?? '') . ' ' . ($activity->staff->lastname ?? '')) ?: '—' }}</div>
                </div>
            @endif
        </section>

        <table class="doc-a-items">
            <thead>
                <tr>
                    <th class="doc-a-line">N.º</th>
                    <th>Descripción</th>
                    <th class="doc-a-qty">Cant.</th>
                    <th class="doc-a-amount">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    @php $isBundle = $item->itemable instanceof \App\Models\Bundle\Bundle; @endphp
                    <tr>
                        <td class="doc-a-line doc-num">{{ $loop->iteration }}</td>
                        <td>
                            <div class="doc-a-item-title">{{ $item->itemable->title ?? 'Producto no encontrado' }}</div>
                            <div class="doc-a-item-note">
                                @if($isBundle)
                                    Paquete con {{ $item->itemable->courses->count() }} cursos
                                @elseif($item->itemable)
                                    Curso
                                @endif
                            </div>
                        </td>
                        <td class="doc-a-qty doc-num">{{ ceil($item->quantity) }}</td>
                        <td class="doc-a-amount doc-num">{{ $F::money($item->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="doc-a-totals">
            <div class="doc-a-totals-row"><span>Precio de lista</span><span class="doc-num">{{ $F::money($order->total_before_discount) }}</span></div>
            @if($order->total_discount_amount > 0)
                <div class="doc-a-totals-row"><span>Descuento</span><span class="doc-num">− {{ $F::money($order->total_discount_amount) }}</span></div>
            @endif
            @if($order->total_tax_amount > 0)
                <div class="doc-a-totals-row"><span>Impuestos</span><span class="doc-num">{{ $F::money($order->total_tax_amount) }}</span></div>
            @endif
            <div class="doc-a-totals-row is-grand"><span>Total</span><span class="doc-num">{{ $F::money($order->total_order_amount) }}</span></div>
        </div>

        <footer class="doc-a-foot">
            Comprobante de la orden registrada en {{ config('app.name') }}.
        </footer>

    </article>
