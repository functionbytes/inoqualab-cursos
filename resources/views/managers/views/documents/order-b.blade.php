{{-- order · diseño B — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($order->condition_id);
    $user = $order->user;
    $activity = $order->activity;
    $itemCount = $order->items->count();
@endphp
    <div class="row g-4 align-items-start">

        <div class="col-lg-8">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Qué se compró</h6>
                    <p class="text-muted small mb-0">{{ $itemCount }} {{ $itemCount === 1 ? 'producto' : 'productos' }} en esta orden.</p>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                        @php $isBundle = $item->itemable instanceof \App\Models\Bundle\Bundle; @endphp
                        <div class="doc-b-item">
                            <div>
                                <div class="doc-b-item-title">{{ $item->itemable->title ?? 'Producto no encontrado' }}</div>
                                <div class="doc-b-item-meta">
                                    @if($isBundle)
                                        Paquete: {{ Str::limit($item->itemable->courses->pluck('title')->implode(', '), 120) }}
                                    @elseif($item->itemable)
                                        Curso
                                    @endif
                                </div>
                            </div>
                            <div class="doc-b-item-amount doc-num">
                                {{ $F::money($item->amount) }}
                                <small>{{ ceil($item->quantity) }} {{ ceil($item->quantity) == 1 ? 'unidad' : 'unidades' }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Cliente</h6>
                    <p class="text-muted small mb-0">Quien hizo la compra{{ $activity ? ' y el distribuidor que la gestionó' : '' }}.</p>
                </div>
                <div class="card-body">
                    <dl class="doc-b-facts">
                        <div>
                            <dt>Nombre</dt>
                            <dd>{{ $user ? trim($user->firstname . ' ' . $user->lastname) : 'Usuario eliminado' }}</dd>
                        </div>
                        <div>
                            <dt>Documento</dt>
                            <dd>{{ $user?->identification ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Correo</dt>
                            <dd>{{ $user?->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Teléfono</dt>
                            <dd>{{ $user?->cellphone ?: '—' }}</dd>
                        </div>
                        @if($user?->address)
                            <div>
                                <dt>Dirección</dt>
                                <dd>{{ $user->address }}</dd>
                            </div>
                        @endif
                        @if($activity)
                            <div>
                                <dt>Distribuidor</dt>
                                <dd>{{ $activity->distributor->title ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Empresa</dt>
                                <dd>{{ $activity->enterprise->title ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Encargado</dt>
                                <dd>{{ trim(($activity->staff->firstname ?? '') . ' ' . ($activity->staff->lastname ?? '')) ?: '—' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="doc-b-aside">

                <div class="card">
                    <div class="card-body">
                        <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-b-logo">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="doc-b-total-label">{{ $tone === 'paid' ? 'Total pagado' : 'Total de la orden' }}</div>
                            <span class="badge {{ $tone === 'paid' ? 'bg-success-subtle text-success' : ($tone === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">{{ $order->condition?->title }}</span>
                        </div>
                        <div class="doc-b-total doc-num">{{ $F::money($order->total_order_amount) }}</div>
                        <ul class="doc-b-lines">
                            <li><span>Precio de lista</span><strong class="doc-num">{{ $F::money($order->total_before_discount) }}</strong></li>
                            @if($order->total_discount_amount > 0)
                                <li><span>Descuento{{ $order->coupon ? ' (cupón ' . $order->coupon->code . ')' : '' }}</span><strong class="doc-num">− {{ $F::money($order->total_discount_amount) }}</strong></li>
                            @endif
                            @if($order->total_tax_amount > 0)
                                <li><span>Impuestos</span><strong class="doc-num">{{ $F::money($order->total_tax_amount) }}</strong></li>
                            @endif
                            <li><span>Forma de pago</span><strong>{{ $order->method?->title ?? '—' }}</strong></li>
                            <li><span>Referencia</span><strong>{{ $order->reference ?: '—' }}</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Historial</h6>
                    </div>
                    <div class="card-body">
                        <ol class="doc-b-timeline">
                            <li class="doc-b-event is-done">
                                <div class="doc-b-event-title">Orden creada</div>
                                <div class="doc-b-event-date">{{ $F::date($order->created_at, 'j \d\e F \d\e Y, g:i a') }}</div>
                            </li>
                            @if($tone === 'paid')
                                <li class="doc-b-event is-done">
                                    <div class="doc-b-event-title">Pago recibido</div>
                                    <div class="doc-b-event-date">{{ $order->payment_at ? $F::date($order->payment_at, 'j \d\e F \d\e Y, g:i a') : 'Sin fecha registrada' }}</div>
                                </li>
                            @elseif($tone === 'rejected')
                                <li class="doc-b-event is-rejected">
                                    <div class="doc-b-event-title">Pago rechazado</div>
                                    <div class="doc-b-event-date">Última actualización: {{ $F::date($order->updated_at) }}</div>
                                </li>
                            @else
                                <li class="doc-b-event is-waiting">
                                    <div class="doc-b-event-title">Esperando pago</div>
                                    <div class="doc-b-event-date">Estado actual: {{ Str::lower($order->condition?->title ?? 'sin estado') }}</div>
                                </li>
                            @endif
                        </ol>
                    </div>
                </div>

            </div>
        </div>

    </div>
