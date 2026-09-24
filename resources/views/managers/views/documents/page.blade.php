{{--
    Página de orden / factura / reparto con el diseño elegido (A/B/C), para
    todos los perfiles que usan layouts.managers (manager, contabilidad,
    distribuidor, soporte). Cada controller arma los datos con
    App\Html\DocumentFormat::page().

    Params: $kind (order|invoice|details), $design (a|b|c), $title,
    $breadcrumbs, $actions, $links, y $order | $invoice (+ $details).
--}}
@extends('layouts.managers')

@section('title', $title)

@section('page_header')
    @include('managers.views.documents._header')
@endsection

@section('content')
    @include("managers.views.documents.{$kind}-{$design}")
@endsection

@include('managers.views.documents._assets')
