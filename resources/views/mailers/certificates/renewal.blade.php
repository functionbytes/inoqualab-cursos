@component('mail::message')
# Hola {{ $firstname }}

Tu certificado del curso **{{ $courseTitle }}** vence el **{{ $endAt }}**.

Para mantener tu certificación vigente este nuevo año, renueva tu inscripción al curso. Al renovar generarás una nueva orden y, al completarla, tu certificado quedará vigente por un año más.

@component('mail::button', ['url' => $renewUrl])
Renovar mi curso
@endcomponent

Si ya realizaste la renovación, puedes ignorar este mensaje.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
