@component('mail::message')
# Tu pago está en proceso

Hola {{ $firstname }} {{ $lastname }},

Recibimos tu orden **#{{ $slack }}** y tu pago está siendo verificado. Algunos medios de pago (como PSE) pueden tardar unos minutos. Te avisaremos en cuanto se confirme.

@component('mail::panel')
**Orden:** {{ $slack }}
**Método de pago:** {{ $method }}
**Total:** ${{ number_format((float) $total, 0, ',', '.') }} COP
@endcomponent

@component('mail::button', ['url' => route('customers.orders')])
Ver mis órdenes
@endcomponent

No es necesario volver a pagar.<br>
{{ config('app.name') }}
@endcomponent
