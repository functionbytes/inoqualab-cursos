@extends('layouts.customers')

@section('title', 'Notificaciones')

@php
    // Esta vista es la única del portal sin @section('context-title'): la
    // banda de contexto azul (context-band.blade.php) se salta por completo
    // con @hasSection('context-title') cuando una vista no la define -- acá
    // faltaba sin querer, no era intencional como en dashboard/configuración
    // (esas sí traen su propia cabecera). $notifications ya llega agrupado
    // por fecha desde el controller.
    $totalNotifications = collect($notifications)->flatten(1)->count();
@endphp

@section('context-title', 'Notificaciones')
@section('context-icon')@include('customers.includes.icon', ['name' => 'bell'])@endsection
@section('context-subtitle', 'Avisos sobre tus cursos, pedidos y certificados')
@section('context-stat-number', $totalNotifications)
{{-- Str::plural('notificación', ...) da "notificacións" (reglas en inglés) --}}
@section('context-stat-label', $totalNotifications === 1 ? 'notificación' : 'notificaciones')

@section('content')
<section class="pnl-section" id="chatsContent"
         data-mark-url="{{ route('customers.notifications.mark') }}"
         data-delete-url="{{ route('customers.notifications.delete') }}">
    @include('customers.partials.views.chats.list')
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/chats/index.js') }}"></script>
@endpush
