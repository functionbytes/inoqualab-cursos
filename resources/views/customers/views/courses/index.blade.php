@extends('layouts.customers')

@section('title', 'Mis cursos')

@section('context-title', 'Mis cursos')
@section('context-icon')@include('customers.includes.icon', ['name' => 'cap'])@endsection
@section('context-subtitle', 'Continúa donde lo dejaste o inscríbete a uno nuevo')
@section('context-stat-number', $courses->total())
@section('context-stat-label', Str::plural('curso', $courses->total()))

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush

@section('content')
<section class="pnl-section" id="coursesContent">
    @include('customers.partials.views.courses.list')
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/courses/index.js') }}"></script>
@endpush
