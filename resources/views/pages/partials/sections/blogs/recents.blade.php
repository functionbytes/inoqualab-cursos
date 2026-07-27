 @if (count($recents) > 0)
    <div class="widget widget-recent-courses wow fadeInUp delay-0-2s animated" style="visibility: visible; animation-name: fadeInUp;">
        <h4 class="widget-title">Blogs recientes</h4>
        <ul>
            @foreach ($recents as $recent)
                <li>
                    <div class="image">
                        {{-- La tabla blogs no tiene columna `image`: la portada vive en
                             Media Library (colección thumbnail), igual que en BlogController. --}}
                        <img src="{{ $recent->getFirstMediaUrl('thumbnail') ?: asset('/pages/images/blog/default.jpg') }}"
                             alt="{{ $recent->title }}" loading="lazy">
                    </div>
                    <div class="content">
                        <h6><a href="{{ route('blogs.view', $recent->slug) }}">{{ substr(strip_tags($recent->title), 0, 400) }}</a></h6>
                        <span>By <a href="#">Williams</a></span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
 @endif
