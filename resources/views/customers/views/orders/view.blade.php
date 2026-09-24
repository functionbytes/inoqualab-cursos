@extends('layouts.customers')

@section('title', 'Pedido '.$order->slack)

@php
    // Estados del pedido según condition_id (mismo criterio que el listado):
    // 1,2 = pendiente/generada · 3 = fallida/reintentar · 4 = pagada.
    $cid = (int) $order->condition_id;
    $pagada = $cid === 4;
    $puedePagar = in_array($cid, [1, 2, 3], true) && (float) $order->total_order_amount > 0;
    $reintento = $cid === 3;

    $condSlug = optional($order->condition)->slug;
    $condTitle = optional($order->condition)->title ?? 'Sin estado';

    $cliente = trim(
        \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($order->user->firstname ?? ''))
        .' '.\Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($order->user->lastname ?? ''))
    ) ?: 'N/D';

    $money = fn ($v) => '$ '.number_format((float) $v, 0, ',', '.');
@endphp

@section('content')
<section class="pnl-section">

    @php
        $heroState = $pagada ? 'ok' : ($puedePagar ? 'pending' : 'neutral');
        $heroTitle = $pagada
            ? 'Ya tienes acceso a tu capacitación'
            : ($puedePagar ? 'Completa el pago para activar tu acceso' : $condTitle);
        $heroTotalLabel = $pagada ? 'Total pagado' : ($puedePagar ? 'Total a pagar' : 'Total del pedido');
    @endphp

    <div class="ovw-hero ovw-hero--{{ $heroState }}">
        <div class="ovw-hero-row">
            <div class="ovw-hero-id">
                <span class="ic">
                    @include('customers.includes.icon', ['name' => $pagada ? 'circle-check' : ($puedePagar ? 'clock' : 'receipt')])
                </span>
                <div>
                    <div class="eyebrow">Pedido {{ $order->slack }} · {{ $condTitle }}</div>
                    <h1>{{ $heroTitle }}</h1>
                    <div class="date">
                        {{ $order->payment_at
                            ? 'Pagado el '.\Carbon\Carbon::parse($order->payment_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                            : 'Creado el '.\Carbon\Carbon::parse($order->created_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                    </div>
                </div>
            </div>
            <div class="ovw-hero-total">
                <span>{{ $heroTotalLabel }}</span>
                <b>{{ $money($order->total_order_amount) }}</b>
            </div>
        </div>
    </div>

    <div class="ovw-wrap">

        {{-- ===== Columna principal: recibo (propuesta A del /design: ícono
             por ítem + footer de totales con fondo resaltado) ===== --}}
        <div class="ovw-main">
            <div class="ovw-card">
                <div class="ovw-card-title">Artículos del pedido</div>

                <div class="ovw-cols">
                    <span>Artículo</span>
                    <span>Valor</span>
                </div>

                <div class="ovw-items">
                    @forelse($items as $item)
                        @php
                            $itemable = $item->itemable;
                            $esBundle = $item->item_type && str_contains($item->item_type, 'Bundle');
                        @endphp
                        <div class="ovw-item">
                            <div class="txt">
                                <span class="tag">{{ $esBundle ? 'Paquete' : 'Curso' }}</span>
                                <b>{{ optional($itemable)->title ?? 'Artículo del pedido' }}</b>
                                @if(optional(optional($itemable)->categorie)->title)
                                    <span class="cat">{{ $itemable->categorie->title }}</span>
                                @endif
                            </div>
                            <div class="price">{{ $money($item->amount) }}</div>
                        </div>
                    @empty
                        <div class="ovw-empty-items">Este pedido no tiene artículos registrados.</div>
                    @endforelse
                </div>

                @if($items->hasPages())
                    <div class="ovw-pag">
                        {{ $items->links() }}
                    </div>
                @endif

                {{-- El footer de totales siempre se muestra (evita que el
                     pedido termine sin un cierre visual en esta tarjeta); el
                     desglose Subtotal/Descuento solo aparece cuando de verdad
                     hubo un cupón aplicado -- sin descuento sería repetir el
                     mismo número dos veces sin aportar nada nuevo. --}}
                <div class="ovw-totals">
                    @if((float) $order->total_discount_amount > 0)
                        <div class="ovw-tot-row">
                            <span>Subtotal</span>
                            <b>{{ $money($order->total_before_discount) }}</b>
                        </div>
                        <div class="ovw-tot-row">
                            <span>
                                Descuento
                                @if($order->coupon)
                                    <span class="coupon">{{ Str::upper($order->coupon->code) }}</span>
                                @endif
                            </span>
                            <b class="disc">-{{ $money($order->total_discount_amount) }}</b>
                        </div>
                    @endif
                    <div class="ovw-tot-row ovw-tot-final">
                        <span>Total</span>
                        <b>{{ $money($order->total_order_amount) }}</b>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Panel lateral: un solo componente (propuesta A del /design) =====
             Antes: botones sueltos + una .ovw-info-card por sección, cada una
             con su propio borde/sombra/radius. Ahora es una sola tarjeta con
             las secciones (acciones, Cliente, Empresa/Distribuidor, Pago,
             Acceso) separadas por border-top interno. --}}
        <aside class="ovw-side">
            <div class="ovw-sidebar-card">

                <div class="ovw-sb-actions">
                    @if($puedePagar)
                        <a href="{{ route('customers.orders.payments', $order->slack) }}" class="ovw-btn solid">
                            {{ $reintento ? 'Reintentar pago' : 'Pagar ahora' }}
                        </a>
                    @endif

                    @if($pagada)
                        <a href="{{ route('customers.orders.invoice', $order->slack) }}" class="ovw-btn {{ $puedePagar ? 'ghost' : 'solid' }}" target="_blank">
                            Descargar recibo
                        </a>
                    @endif

                    <a href="{{ route('customers.orders') }}" class="ovw-btn ghost">
                        Volver a mis pedidos
                    </a>
                </div>

                {{-- Cliente --}}
                <div class="ovw-sb-section">
                    <div class="ovw-info-label">Cliente</div>
                    <div class="ovw-info-name">{{ $cliente }}</div>
                    @if($order->user->identification ?? false)
                        <div class="ovw-info-sub">Doc. {{ $order->user->identification }}</div>
                    @endif
                    @if($order->user->email ?? false)
                        <div class="ovw-info-sub">{{ $order->user->email }}</div>
                    @endif
                    @if($order->user->address ?? false)
                        <div class="ovw-info-sub">{{ Str::ucfirst(Str::lower($order->user->address)) }}</div>
                    @endif
                </div>

                {{-- Empresa / Distribuidor: solo si el pedido se gestionó a
                     través de uno (orders_activity) -- las compras directas
                     del cliente en el checkout no tienen esta relación. --}}
                @if($order->activity)
                    <div class="ovw-sb-section">
                        <div class="ovw-info-label">Empresa / Distribuidor</div>
                        @if($order->activity->distributor)
                            <div class="ovw-info-name">{{ $order->activity->distributor->title }}</div>
                        @endif
                        @if($order->activity->enterprise)
                            <div class="ovw-info-sub">{{ $order->activity->enterprise->title }}</div>
                        @endif
                        @if($order->activity->staff)
                            <div class="ovw-info-sub">Encargado: {{ $order->activity->staff->firstname }} {{ $order->activity->staff->lastname }}</div>
                        @endif
                    </div>
                @endif

                {{-- Pago --}}
                <div class="ovw-sb-section">
                    <div class="ovw-info-label">Pago</div>
                    <div class="ovw-info-row">
                        <span>Referencia</span>
                        <b>{{ $order->reference }}</b>
                    </div>
                    @if($order->type)
                        <div class="ovw-info-row">
                            <span>Tipo</span>
                            <b>{{ $order->type->title }}</b>
                        </div>
                    @endif
                    @if($order->method)
                        <div class="ovw-info-row">
                            <span>Método</span>
                            <b>{{ $order->method->title }}</b>
                        </div>
                    @endif
                    <div class="ovw-info-row">
                        <span>Creada</span>
                        <b>{{ \Carbon\Carbon::parse($order->created_at)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                    </div>
                    @if($order->payment_at)
                        <div class="ovw-info-row">
                            <span>Pagada</span>
                            <b>{{ \Carbon\Carbon::parse($order->payment_at)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                        </div>
                    @endif
                </div>

                {{-- Acceso --}}
                @if($order->inscription && ($order->inscription->enroll_start || $order->inscription->enroll_expire))
                    <div class="ovw-sb-section">
                        <div class="ovw-info-label">Acceso</div>
                        @if($order->inscription->enroll_start)
                            <div class="ovw-info-row">
                                <span>Inicio</span>
                                <b>{{ \Carbon\Carbon::parse($order->inscription->enroll_start)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                            </div>
                        @endif
                        @if($order->inscription->enroll_expire)
                            <div class="ovw-info-row">
                                <span>Vence</span>
                                <b>{{ \Carbon\Carbon::parse($order->inscription->enroll_expire)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </aside>

    </div>

</section>
@endsection
