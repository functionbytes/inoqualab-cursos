@extends('layouts.customers')

@section('title', 'Instrucciones')

@section('context-title', 'Instrucciones')
@section('context-icon')@include('customers.includes.icon', ['name' => 'book'])@endsection
@section('context-subtitle', 'Guías y procedimientos para tu proceso de formación')
@section('context-stat-number', $instructions->count())
@section('context-stat-label', Str::plural('guía', $instructions->count()))

@section('content')
<section class="pnl-section">

    @if($instructions->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'book'])</span>
            <h3>Aún no hay instrucciones publicadas</h3>
            <p>Aquí aparecerán las guías y procedimientos que INOQUALAB comparta sobre tus cursos, exámenes y certificados.</p>
            <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
        </div>

    @else

    <div class="lb-wrap">

        <aside class="lb-side">
            <div class="lb-tree">
                <div class="eyebrow">Categorías</div>
                <span class="item is-active" data-category-id="all">
                    Todas<b>{{ $instructions->count() }}</b>
                </span>
                @foreach($categories as $categorie)
                    <span class="item" data-category-id="{{ $categorie->id }}">
                        {{ $categorie->title }}<b>{{ $categorie->instructions_count ?? $instructions->where('category_id', $categorie->id)->count() }}</b>
                    </span>
                @endforeach
                <p class="note">Las categorías las define INOQUALAB según el tema de cada guía.</p>
            </div>
        </aside>

        <div class="lb-main">
            <div class="lb-head">
                <div>
                    {{-- El título ya lo muestra la banda de contexto del header. --}}
                    <div class="sub" id="insListSub">{{ $instructions->count() }} {{ Str::plural('guía disponible', $instructions->count()) }}</div>
                </div>
            </div>

            <div class="ins-list" id="insList" data-filter-url="{{ route('customers.instructions.filter') }}">
                @include('customers.partials.views.instructions.list', ['instructions' => $instructions])
            </div>
        </div>

    </div>

    @endif

</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/instructions/index.js') }}"></script>
@endpush
