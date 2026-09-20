@extends('layouts.customers')

@section('title', $instruction->title)

@section('context-title', $instruction->title)
@section('context-icon')@include('customers.includes.icon', ['name' => 'book'])@endsection
@section('context-subtitle', $instruction->categorie->title ?? 'Instrucciones')

@section('content')
<section class="pnl-section">

    <a class="ins-back" href="{{ route('customers.instructions') }}">
        Volver a instrucciones
    </a>

    <div class="ins-doc">
        <div class="ins-doc-head">
            <span class="ins-badge">{{ $instruction->categorie->title ?? 'General' }}</span>
            <h1>{{ $instruction->title }}</h1>
        </div>
        <div class="ins-doc-body lv-pane">
            {!! clean($instruction->description, 'content') !!}
        </div>
    </div>

</section>
@endsection
