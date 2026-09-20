@extends('layouts.customers')

@section('title', 'Mis certificados')

@section('context-title', 'Mis certificados')
@section('context-icon')@include('customers.includes.icon', ['name' => 'award'])@endsection
@section('context-subtitle', 'Descarga los certificados de tus cursos aprobados')
@section('context-stat-number', $certificates->total())
@section('context-stat-label', Str::plural('certificado', $certificates->total()))

@section('content')
<section class="pnl-section" id="certificatesContent">
    @include('customers.partials.views.certificates.list')
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/certificates/index.js') }}"></script>
@endpush
