{{-- order · diseño C — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($order->condition_id);
    $user = $order->user;
    $activity = $order->activity;
@endphp
    <div class="doc-c">

        <div class="doc-c-band">
            <div class="doc-c-band-id">
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-c-logo">
                <span class="doc-c-band-rule" aria-hidden="true"></span>
                <div>
                <div class="doc-c-band-kind">Orden de compra</div>
                <div class="doc-c-band-code">{{ $order->slack }}</div>
                </div>
            </div>
            <span class="doc-c-state is-{{ $tone }}">{{ $order->condition?->title ?? 'Sin estado' }}</span>
        </div>

        <div class="doc-c-grid">
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Cliente</div>
                <div class="doc-c-cell-value">{{ $user ? trim($user->firstname . ' ' . $user->lastname) : 'Usuario eliminado' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Documento</div>
                <div class="doc-c-cell-value">{{ $user?->identification ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Teléfono</div>
                <div class="doc-c-cell-value">{{ $user?->cellphone ?: '—' }}</div>
            </div>
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Correo</div>
                <div class="doc-c-cell-value">{{ $user?->email ?? '—' }}</div>
            </div>
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Dirección</div>
                <div class="doc-c-cell-value">{{ $user?->address ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Referencia</div>
                <div class="doc-c-cell-value">{{ $order->reference ?: '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Emitida</div>
                <div class="doc-c-cell-value">{{ $F::date($order->created_at, 'd/m/Y H:i') }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Pagada</div>
                <div class="doc-c-cell-value">{{ $order->payment_at ? $F::date($order->payment_at, 'd/m/Y H:i') : '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Forma de pago</div>
                <div class="doc-c-cell-value">{{ $order->method?->title ?? '—' }} ({{ Str::lower($order->type?->title ?? '') }})</div>
            </div>
            @if($activity)
                <div class="doc-c-cell is-wide">
                    <div class="doc-c-cell-label">Distribuidor</div>
                    <div class="doc-c-cell-value">{{ $activity->distributor->title ?? '—' }}</div>
                </div>
                <div class="doc-c-cell">
                    <div class="doc-c-cell-label">Empresa</div>
                    <div class="doc-c-cell-value">{{ $activity->enterprise->title ?? '—' }}</div>
                </div>
                <div class="doc-c-cell">
                    <div class="doc-c-cell-label">Encargado</div>
                    <div class="doc-c-cell-value">{{ trim(($activity->staff->firstname ?? '') . ' ' . ($activity->staff->lastname ?? '')) ?: '—' }}</div>
                </div>
            @endif
        </div>

        <div class="doc-c-section">Productos</div>
        <div class="doc-c-table-wrap">
            <table class="doc-c-table">
                <thead>
                    <tr>
                        <th class="doc-c-line">N.º</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th class="is-center">Cant.</th>
                        <th class="is-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php $isBundle = $item->itemable instanceof \App\Models\Bundle\Bundle; @endphp
                        <tr>
                            <td class="doc-c-line doc-num">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $item->itemable->title ?? 'Producto no encontrado' }}</td>
                            <td>{{ $isBundle ? 'Paquete (' . $item->itemable->courses->count() . ' cursos)' : ($item->itemable ? 'Curso' : '—') }}</td>
                            <td class="is-center doc-num">{{ ceil($item->quantity) }}</td>
                            <td class="is-end doc-num">{{ $F::money($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="doc-c-sum">
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Precio de lista</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($order->total_before_discount) }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Descuento{{ $order->coupon ? ' (cupón ' . $order->coupon->code . ')' : '' }}</div>
                <div class="doc-c-cell-value doc-num">{{ $order->total_discount_amount > 0 ? '− ' . $F::money($order->total_discount_amount) : $F::money(0) }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Impuestos</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($order->total_tax_amount) }}</div>
            </div>
            <div class="doc-c-cell is-total">
                <div class="doc-c-cell-label">Total</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($order->total_order_amount) }}</div>
            </div>
        </div>

    </div>
