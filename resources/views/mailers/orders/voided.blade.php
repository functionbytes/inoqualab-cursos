@component('mail::message')
# No pudimos procesar tu pago

Hola {{ $firstname }} {{ $lastname }},

Tu transacción de la orden **#{{ $slack }}** fue rechazada y no se realizó ningún cobro. Esto suele ocurrir por fondos insuficientes o restricciones del banco.

@component('mail::panel')
**Orden:** {{ $slack }}
**Método de pago:** {{ $method }}
**Total:** ${{ number_format((float) $total, 0, ',', '.') }} COP
@endcomponent

Puedes intentarlo nuevamente desde tus órdenes:

@component('mail::button', ['url' => route('customers.orders'), 'color' => 'error'])
Reintentar el pago
@endcomponent

Si el problema persiste, contáctanos.<br>
{{ config('app.name') }}
@endcomponent
