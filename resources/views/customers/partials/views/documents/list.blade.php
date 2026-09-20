{{-- Contenido de Documentos: buscador, filtro por tipo, lista y paginador.
     Vive en un partial aparte para poder re-renderizarlo por AJAX (ver el
     script en documents/index.blade.php) sin recargar la página. $type y
     $typeCounts ya vienen calculados desde el controller -- la extensión no
     es una columna, sale del nombre del archivo en la media asociada. --}}
@php
    $typeLabels = ['pdf' => 'PDF', 'word' => 'Word', 'excel' => 'Excel', 'image' => 'Imágenes', 'other' => 'Otros'];
@endphp

@if($documents->isEmpty())

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            {{-- El título ya lo muestra la banda de contexto del header. --}}
            <div class="sub">Material de apoyo y constancias que INOQUALAB comparte contigo</div>
        </div>
        <form class="pnl-search" action="{{ route('customers.documents') }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            {{-- Hidden: un form GET descarta el query string del "action" --
                 sin esto, buscar desde una pestaña de tipo filtrada volvía
                 siempre a "Todos", igual que $condition en Mis pedidos. --}}
            @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
            <input type="search" name="search" placeholder="Buscar documento…" autocomplete="off"
                   value="{{ $searchKey ?? '' }}" aria-label="Buscar documento">
        </form>
    </div>

    <div class="pnl-gap"></div>

    @if($typeCounts['todos'] > 0)
        <div class="pnl-filter" role="group" aria-label="Filtrar documentos por tipo">
            <a href="{{ route('customers.documents', array_filter(['search' => $searchKey])) }}"
               class="{{ $type ? '' : 'active' }}">
                Todos<span class="cnt">{{ $typeCounts['todos'] }}</span>
            </a>
            @foreach($typeLabels as $key => $label)
                @continue($typeCounts[$key] === 0)
                <a href="{{ route('customers.documents', array_filter(['search' => $searchKey, 'type' => $key])) }}"
                   class="{{ $type === $key ? 'active' : '' }}">
                    {{ $label }}<span class="cnt">{{ $typeCounts[$key] }}</span>
                </a>
            @endforeach
        </div>
        <div class="pnl-gap"></div>
    @endif

    @if($searchKey)
        <div class="od-searching">
            Resultados para <b>{{ $searchKey }}</b>
            <a href="{{ route('customers.documents') }}">Quitar búsqueda</a>
        </div>
    @endif

    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'folder'])</span>
        @if($searchKey)
            <h3>Ningún documento coincide con «{{ $searchKey }}»</h3>
            <p>Prueba con otras palabras o quita la búsqueda para ver todo el material disponible.</p>
            <a href="{{ route('customers.documents') }}">Ver todos los documentos</a>
        @elseif($type)
            <h3>No hay documentos de este tipo</h3>
            <p>Prueba con otra pestaña para ver el resto del material disponible.</p>
            <a href="{{ route('customers.documents') }}">Ver todos los documentos</a>
        @else
            <h3>Todavía no hay documentos</h3>
            <p>Aquí aparecerá el material de apoyo de tus cursos y las constancias que INOQUALAB publique para ti.</p>
            <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
        @endif
    </div>

@else

    {{-- Título, buscador, filtro, filas y el pie con el paginador viven en la
         misma caja -- antes el título+buscador eran una tarjeta aparte,
         con un espacio visible antes de la lista. --}}
    <div class="dc-list">
        <div class="od-head">
            <div class="sub">Material de apoyo y constancias que INOQUALAB comparte contigo</div>
            <form class="pnl-search" action="{{ route('customers.documents') }}" method="GET" role="search">
                @include('customers.includes.icon', ['name' => 'search'])
                @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                <input type="search" name="search" placeholder="Buscar documento…" autocomplete="off"
                       value="{{ $searchKey ?? '' }}" aria-label="Buscar documento">
            </form>
        </div>

        @if($typeCounts['todos'] > 0)
            <div class="pnl-filter pnl-filter-flat" role="group" aria-label="Filtrar documentos por tipo">
                <a href="{{ route('customers.documents', array_filter(['search' => $searchKey])) }}"
                   class="{{ $type ? '' : 'active' }}">
                    Todos<span class="cnt">{{ $typeCounts['todos'] }}</span>
                </a>
                @foreach($typeLabels as $key => $label)
                    @continue($typeCounts[$key] === 0)
                    <a href="{{ route('customers.documents', array_filter(['search' => $searchKey, 'type' => $key])) }}"
                       class="{{ $type === $key ? 'active' : '' }}">
                        {{ $label }}<span class="cnt">{{ $typeCounts[$key] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if($searchKey)
            <div class="od-searching od-searching-inline">
                Resultados para <b>{{ $searchKey }}</b>
                <a href="{{ route('customers.documents') }}">Quitar búsqueda</a>
            </div>
        @endif

        @foreach($documents as $document)
            @php
                // Extensión y peso salen de la media asociada. Inline (no un
                // closure externo): este partial también se renderiza solo,
                // desde la respuesta AJAX del controller.
                $media = $document->getFirstMedia('files');
                $file = null;
                if ($media) {
                    $ext = \Illuminate\Support\Str::upper(pathinfo($media->file_name, PATHINFO_EXTENSION)) ?: 'ARCHIVO';
                    $bytes = (int) $media->size;
                    $peso = $bytes >= 1048576
                        ? number_format($bytes / 1048576, 1, ',', '.').' MB'
                        : max(1, (int) round($bytes / 1024)).' KB';
                    $file = (object) ['url' => $media->getFullUrl(), 'ext' => $ext, 'peso' => $peso];
                }
            @endphp

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
                            Descargar
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
