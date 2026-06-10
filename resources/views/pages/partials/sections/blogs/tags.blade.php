 @if (count($tags) > 0)
    <div class="widget widget-tag-cloud wow fadeInUp delay-0-2s animated">
        <h4 class="widget-title">Etiquetas destacadas</h4>
        <div class="tag-coulds">
            @foreach ($tags as $tag)
                <a href="{{ route('blogs.tags', $tag->slug) }}">{{ $tag->title }} </a>
            @endforeach
        </div>
    </div>
 @endif
