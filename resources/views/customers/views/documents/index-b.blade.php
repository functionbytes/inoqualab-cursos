@extends('layouts.customers')

@section('title', 'Mi biblioteca')

@php
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

    // Agrupa por extensión para el panel lateral: es el único criterio que se
    // puede derivar del archivo sin inventar categorías que no existen en la BD.
    $porTipo = $documents->getCollection()
        ->map(fn ($d) => optional($meta($d))->ext ?? 'OTROS')
        ->countBy();
@endphp

@section('content')
<section class="pnl-section">

    @if($documents->isEmpty())

        <div class="cd-head">
            <h2>Mi biblioteca</h2>
            <div class="sub">Material de apoyo y constancias que INOQUALAB comparte contigo</div>
        </div>

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'folder'])</span>
            @if($searchKey)
                <h3>Ningún documento coincide con «{{ $searchKey }}»</h3>
                <p>Prueba con otras palabras o quita la búsqueda para ver todo el material disponible.</p>
                <a href="{{ route('customers.documents') }}">Ver todos los documentos</a>
            @else
                <h3>Tu biblioteca está vacía</h3>
                <p>Aquí aparecerá el material de apoyo de tus cursos y las constancias que INOQUALAB publique para ti.</p>
                <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
            @endif
        </div>

    @else

    <div class="lb-wrap">

        <aside class="lb-side">
            <div class="lb-tree">
                <div class="eyebrow">Biblioteca</div>
                <span class="item is-active">
                    @include('customers.includes.icon', ['name' => 'folder'])
                    Todos<b>{{ $documents->total() }}</b>
                </span>
                @foreach($porTipo as $tipo => $n)
                    <span class="item">
                        @include('customers.includes.icon', ['name' => 'receipt'])
                        {{ $tipo }}<b>{{ $n }}</b>
                    </span>
                @endforeach
                <p class="note">Los documentos los publica INOQUALAB según los cursos en los que estás inscrito.</p>
            </div>
        </aside>

        <div class="lb-main">
            <div class="lb-head">
                <div>
                    <h2>Mi biblioteca</h2>
                    <div class="sub">{{ $documents->total() }} {{ $documents->total() === 1 ? 'documento disponible' : 'documentos disponibles' }}</div>
                </div>
                <form class="pnl-search" action="{{ Request::fullUrl() }}" method="GET" role="search">
                    @include('customers.includes.icon', ['name' => 'search'])
                    <input type="search" name="search" placeholder="Buscar documento…" autocomplete="off"
                           value="{{ $searchKey ?? '' }}" aria-label="Buscar documento">
                </form>
            </div>

            <div class="lb-grid">
                @foreach($documents as $document)
                    @php $file = $meta($document); @endphp

                    <article class="lb-card">
                        <div class="lb-cover">
                            <div class="paper">
                                <span class="ext">{{ $file->ext ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="lb-body">
                            <b>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::lower($document->title)) }}</b>
                            <span>
                                {{ \Carbon\Carbon::parse($document->updated_at)->locale('es')->isoFormat('D MMM YYYY') }}
                                @if($file) · {{ $file->peso }} @endif
                            </span>
                            @if($file)
                                <a target="_blank" href="{{ $file->url }}">Descargar</a>
                            @else
                                <span class="dc-none">Archivo no disponible</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="od-foot bare">
                <span>Mostrando {{ $documents->firstItem() }}-{{ $documents->lastItem() }} de {{ $documents->total() }} resultados</span>
                <nav>{{ $documents->appends(request()->input())->links() }}</nav>
            </div>
        </div>

    </div>

    @endif

</section>
@endsection
