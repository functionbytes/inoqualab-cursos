{{--
    Selector de diseño de orden/factura. Cambia solo la vista en pantalla
    (?diseno=); el diseño por defecto se elige en Configuración de facturación.
    Params: $design (null = vista original).
--}}
@php
    $currentDesign = $design ?? 'original';
@endphp
<div class="design-switcher" role="group" aria-label="Diseño del documento">
    @foreach(\App\Html\DocumentFormat::DESIGNS as $key => $label)
        <a href="{{ url()->current() }}?diseno={{ $key }}" class="design-switcher-item {{ $currentDesign === $key ? 'is-active' : '' }}"
           @if($currentDesign === $key) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</div>
