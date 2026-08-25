@extends('layouts.customers')

@section('title', 'Documentos')

@php
    // Extensión y peso salen de la media asociada: la tabla anterior solo
    // mostraba título y fecha, así que no se sabía qué se iba a descargar.
    $meta = function ($document) {
        $media = $document->getFirstMedia('files');

        if (! $media) {
            return null;
        }

        $ext = \Illuminate\Support\Str::upper(pathinfo($media->file_name, PATHINFO_EXTENSION)) ?: 'ARCHIVO';
        $bytes = (int) $media->size;
        $peso = $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '.').' MB'
            : max(1, (int) round($bytes / 1024)).' KB';

        return (object) ['url' => $media->getFullUrl(), 'ext' => $ext, 'peso' => $peso];
    };
@endphp

@section('content')
<section class="pnl-section">

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            <h2>Documentos</h2>
            <div class="sub">Material de apoyo y constancias que INOQUALAB comparte contigo</div>
        </div>
        <form class="pnl-search" action="{{ Request::fullUrl() }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            <input type="search" name="search" placeholder="Buscar documento…" autocomplete="off"
                   value="{{ $searchKey ?? '' }}" aria-label="Buscar documento">
        </form>
    </div>

    <div class="pnl-gap"></div>

    @if($searchKey)
        <div class="od-searching">
            Resultados para <b>{{ $searchKey }}</b>
            <a href="{{ route('customers.documents') }}">Quitar búsqueda</a>
        </div>
    @endif

    @if($documents->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'folder'])</span>
            @if($searchKey)
                <h3>Ningún documento coincide con «{{ $searchKey }}»</h3>
                <p>Prueba con otras palabras o quita la búsqueda para ver todo el material disponible.</p>
                <a href="{{ route('customers.documents') }}">Ver todos los documentos</a>
            @else
                <h3>Todavía no hay documentos</h3>
                <p>Aquí aparecerá el material de apoyo de tus cursos y las constancias que INOQUALAB publique para ti.</p>
                <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
            @endif
        </div>

    @else

        <div class="dc-list">
            @foreach($documents as $document)
                @php $file = $meta($document); @endphp

                <div class="dc-row">
                    <span class="dc-ext ext-{{ $file ? \Illuminate\Support\Str::lower($file->ext) : 'none' }}">
                        {{ $file->ext ?? '—' }}
                    </span>

                    <div class="dc-txt">
                        <b>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::lower($document->title)) }}</b>
                        <span>
                            {{ \Carbon\Carbon::parse($document->updated_at)->locale('es')->isoFormat('D MMM YYYY') }}
                            @if($file) · {{ $file->peso }} @endif
                        </span>
                    </div>

                    <div class="dc-act">
                        @if($file)
                            <a class="solid" target="_blank" href="{{ $file->url }}">
                                @include('customers.includes.icon', ['name' => 'download']) Descargar
                            </a>
                        @else
                            <span class="dc-none">Archivo no disponible</span>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="od-foot">
                <span>Mostrando {{ $documents->firstItem() }}-{{ $documents->lastItem() }} de {{ $documents->total() }} resultados</span>
                <nav>{{ $documents->appends(request()->input())->links() }}</nav>
            </div>
        </div>

    @endif

</section>
@endsection
