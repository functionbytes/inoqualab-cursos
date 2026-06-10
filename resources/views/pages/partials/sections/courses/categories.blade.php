@if (count($categories) > 0)
    <div class="widget widget-menu wow fadeInUp delay-0-4s">
        <h4 class="widget-title">Categorias</h4>
        <ul>
            @foreach ($categories as $categorie)
                <li><a href="/courses?categorie={{$categorie->slug}}">{{ $categorie->title }} <span>({{ count($categorie->courses) }})</span></a> </li>
            @endforeach
        </ul>
    </div>
@endif