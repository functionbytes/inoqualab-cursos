 @if (count($recents) > 0)
    <div class="widget widget-recent-courses wow fadeInUp delay-0-2s animated" style="visibility: visible; animation-name: fadeInUp;">
        <h4 class="widget-title">Blogs recientes</h4>
        <ul>
            @foreach ($recents as $recent)
                <li>
                    <div class="image">
                        @if ($recent->image != null)
                            <img src="{{ asset('/pages/images/blog/' . $recent- loading="lazy">image) }}" alt="image">
                        @else
                            <img src="{{ asset('/pages/images/blog/default.jpg') }}" alt="image" loading="lazy">
                        @endif
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
