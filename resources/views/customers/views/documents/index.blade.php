@extends('layouts.customers')

@section('title', 'Documentos')

@section('context-title', 'Documentos')
@section('context-icon')@include('customers.includes.icon', ['name' => 'folder'])@endsection
@section('context-subtitle', 'Material de apoyo disponible para tus cursos')
@section('context-stat-number', $documents->total())
@section('context-stat-label', Str::plural('documento', $documents->total()))

@section('content')
<section class="pnl-section" id="documentsContent">
    @include('customers.partials.views.documents.list')
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/documents/index.js') }}"></script>
@endpush
