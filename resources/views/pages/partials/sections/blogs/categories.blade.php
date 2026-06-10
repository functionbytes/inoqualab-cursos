@if (count($categories) > 0)

    <div class="widget widget-menu wow fadeInUp delay-0-2s animated" style="visibility: visible; animation-name: fadeInUp;">
        <h4 class="widget-title">Categorias</h4>
        <ul>
            @foreach ($categories as $categorie)
                <li>
                    <a href="{{ route('blogs.categories', [$categorie->slug]) }}">{{ $categorie->title }}
                        <span >({{ count($categorie->blogs) }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

@endif
