
      @if(isset($blogs))
            @foreach($blogs as $blog)

                 <a href="{{ route('blogs.view',$blog->slug) }}"  class="blog-standard-wrap">

                     <div class="blog-standard-item wow fadeInUp delay-0-2s animated">
                         <div class="image">
                             @if(count($blog->getMedia('thumbnail'))>0)
                                 <img src="{{ $blog->getfirstMedia('thumbnail')->getfullUrl() }}" class="card-img-top rounded-0 object-fit-cover js-img-fallback" alt="{{ $blog->title }}" height="440" loading="lazy"
                                      data-fallback-src="{{ asset('/pages/images/blog/default.jpg') }}">
                             @else
                                 <img src="{{ asset('/pages/images/blog/default.jpg') }}" class="card-img-top rounded-0 object-fit-cover" alt="{{ $blog->title }}" height="440" loading="lazy">
                             @endif
                         </div>
                         <div class="blog-standard-content">
                             <div class="content">
                                 <ul class="blog-standard-header">
                                     <li><span class="name">Administrador</span></li>
                                     <li><i class="far fa-calendar-alt"></i> {{ date('F ,Y', strtotime($blog->created_at)) }}</li>
                                     <li><i class="far fa-comments"></i>Comentarios (0)</li>
                                 </ul>
                                 <h3>{{substr(strip_tags($blog->title), 0, 400)}}.</h3>
                                 <p>{{substr(strip_tags($blog->description), 0, 400)}}.. </p>
                             </div>
                         </div>
                     </div>
                </a>
                
            @endforeach
      @endif
