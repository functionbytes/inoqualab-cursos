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

    <a href="{{ route('customers.orders') }}" class="ovw-back">
        Volver a mis pedidos
    </a>

    <div class="ovw-wrap">

        {{-- ===== Columna principal: recibo ===== --}}
        <div class="ovw-main">
            <div class="ovw-card">
                <div class="ovw-head">
                    <div>
                        <div class="eyebrow">Pedido</div>
                        <h1>{{ $order->slack }}</h1>
                        <div class="date">
                            {{ $order->payment_at
                                ? 'Pagado el '.\Carbon\Carbon::parse($order->payment_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                                : 'Creado el '.\Carbon\Carbon::parse($order->created_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                        </div>
                    </div>
                    <span class="ovw-chip cnd-{{ $condSlug }}">{{ $condTitle }}</span>
                </div>

                <div class="ovw-cols">
                    <span>Artículo</span>
                    <span>Valor</span>
                </div>

                <div class="ovw-items">
                    @forelse($order->items as $item)
                        @php
                            $itemable = $item->itemable;
                            $esBundle = $item->item_type && str_contains($item->item_type, 'Bundle');
                        @endphp
                        <div class="ovw-item">
                            <span class="tag">{{ $esBundle ? 'Paquete' : 'Curso' }}</span>
                            <div class="txt">
                                <b>{{ optional($itemable)->title ?? 'Artículo del pedido' }}</b>
                                @if(optional(optional($itemable)->categorie)->title)
                                    <span>{{ $itemable->categorie->title }}</span>
                                @endif
                            </div>
                            <div class="price">{{ $money($item->amount) }}</div>
                        </div>
                    @empty
                        <div class="ovw-empty-items">Este pedido no tiene artículos registrados.</div>
                    @endforelse
                </div>

                <div class="ovw-totals">
                    @if((float) $order->total_discount_amount > 0)
                        <div class="row">
                            <span>Descuento</span>
                            <b class="disc">-{{ $money($order->total_discount_amount) }}</b>
                        </div>
                    @endif
                    <div class="row total">
                        <span>Total</span>
                        <b>{{ $money($order->total_order_amount) }}</b>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Panel lateral: estado + acción + datos ===== --}}
        <aside class="ovw-side">
            <div class="ovw-status st-{{ $pagada ? 'ok' : ($puedePagar ? 'pending' : 'neutral') }}">
                <span class="ic">
                    @include('customers.includes.icon', ['name' => $pagada ? 'circle-check' : ($puedePagar ? 'clock' : 'receipt')])
                </span>
                <div class="txt">
                    <b>{{ $pagada ? 'Pedido pagado' : ($puedePagar ? 'Pago pendiente' : $condTitle) }}</b>
                    <span>
                        {{ $pagada
                            ? 'Ya tienes acceso a las capacitaciones de este pedido.'
                            : ($puedePagar ? 'Completa el pago para habilitar el acceso al curso.' : 'Este pedido no requiere acción de tu parte.') }}
                    </span>
                </div>
            </div>

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

            <div class="ovw-facts">
                <h4>Datos del pedido</h4>
                <div class="field">
                    <span>Cliente</span>
                    <b>{{ $cliente }}</b>
                </div>
                @if($order->user->email ?? false)
                    <div class="field">
                        <span>Correo</span>
                        <b>{{ $order->user->email }}</b>
                    </div>
                @endif
                @if($order->user->address ?? false)
                    <div class="field">
                        <span>Dirección</span>
                        <b>{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($order->user->address)) }}</b>
                    </div>
                @endif
                @if($order->inscription && $order->inscription->enroll_start)
                    <div class="field">
                        <span>Inicio de acceso</span>
                        <b>{{ \Carbon\Carbon::parse($order->inscription->enroll_start)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                    </div>
                @endif
                @if($order->inscription && $order->inscription->enroll_expire)
                    <div class="field">
                        <span>Vence el acceso</span>
                        <b>{{ \Carbon\Carbon::parse($order->inscription->enroll_expire)->locale('es')->isoFormat('D MMM YYYY') }}</b>
                    </div>
                @endif
            </div>
        </aside>

    </div>

</section>
@endsection
