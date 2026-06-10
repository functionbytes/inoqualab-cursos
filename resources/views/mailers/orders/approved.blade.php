@component('mail::message')
# ¡Pago aprobado!

Hola {{ $firstname }} {{ $lastname }},

Tu pago de la orden **#{{ $slack }}** fue procesado exitosamente. Ya puedes acceder a tu contenido y empezar a estudiar.

@component('mail::panel')
**Orden:** {{ $slack }}
**Método de pago:** {{ $method }}
**Fecha de pago:** {{ $payment }}
**Total pagado:** ${{ number_format((float) $total, 0, ',', '.') }} COP
@endcomponent

@component('mail::button', ['url' => route('customers.courses')])
Ir a mis cursos
@endcomponent

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
@endcomponent
