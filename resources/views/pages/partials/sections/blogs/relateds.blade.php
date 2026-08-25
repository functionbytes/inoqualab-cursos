 @if (count($recents) > 0)
   
 <div class="related-post pt--60">

    <div class="section-title text-start mb--40">
        <h4 class="title">Publicación similar</h4>
    </div>

    @foreach ($recents as $recent)
    <!-- Start Single Card  -->
    <div class="rbt-card card-list variation-02 rbt-hover mt--30">
        <div class="rbt-card-img">
            <a href="{{ route('blogs.view',$recent->slug) }}">
                {{-- La tabla blogs no tiene columna `thumbnail`: la portada vive en
                     Media Library, igual que en BlogController. --}}
                <img src="{{ $recent->cardImageUrl(asset('/pages/images/blog/default.jpg')) }}"
                     alt="{{ $recent->title }}" loading="lazy">
            </a>
        </div>
        <div class="rbt-card-body">
            <h5 class="rbt-card-title"><a href="{{ route('blogs.view',$recent->slug) }}">{{substr(strip_tags($recent->title), 0, 400)}}.</a>
            </h5>
            <div class="rbt-card-bottom">
                <a class="transparent-button" href="{{ route('blogs.view',$recent->slug) }}">Lee más<i><svg width="17" height="12" xmlns="http://www.w3.org/2000/svg"><g stroke="#27374D" fill="none" fill-rule="evenodd"><path d="M10.614 0l5.629 5.629-5.63 5.629"></path><path stroke-linecap="square" d="M.663 5.572h14.594"></path></g></svg></i></a>
            </div>
        </div>
    </div>
    <!-- End Single Card  -->
    @endforeach


    </div>
 @endif