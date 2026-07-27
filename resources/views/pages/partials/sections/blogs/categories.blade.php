@if (count($categories) > 0)

    <div class="widget widget-menu wow fadeInUp delay-0-2s animated" style="visibility: visible; animation-name: fadeInUp;">
        <h4 class="widget-title">Categorias</h4>
        <ul>
            @foreach ($categories as $categorie)
                <li>
                    <a href="{{ route('blogs.categories', [$categorie->slug]) }}">{{ $categorie->title }}
                        {{-- blogs_count viene del withCount del controller (solo publicados).
                             `count($categorie->blogs)` cargaba la relación entera por cada
                             categoría: una consulta extra por fila del widget. --}}
                        <span>({{ $categorie->blogs_count ?? 0 }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

@endif
