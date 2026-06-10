@if(count($relateds)>0)
<section class="course-section rel z-1 pt-120 rpt-90 pb-100 rpb-70 brand1-bg-1">
   <div class="container">
      <div class="row justify-content-center">
         <div class="col-xl-8 col-lg-8 col-md-8">
            <div class="section-title text-center mb-40">
               <h2>Cursos relacionados</h2>
            </div>
         </div>
      </div>
      <div class="row justify-content-center">

         @foreach ($relateds as $related)
            <div class="col-lg-4 col-md-4  col-sm-12">
               <div class="coach-item wow fadeInUp delay-0-2s">
                  <div class="coach-image">
                     <a href="{{ route('courses.view', [$related->slack]) }}" class="category">{{ $related->categorie->title }}</a>
                     @if(count($related->getMedia('thumbnail'))>0)
                        <img src="{{ $related->getfirstMedia('thumbnail')->getfullUrl() }}"
                             class="card-img-top rounded-0 object-fit-cover" alt="..." height="440" loading="lazy"
                             onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                     @else
                        <img src="{{ asset('/pages/images/courses/default.jpg') }}" class="card-img-top rounded-0 object-fit-cover" alt="..." height="440" loading="lazy">
                     @endif
                        </a>
                  </div>
                  <div class="coach-content">
                     <h4><a href="{{ route('courses.view', [$related->slack]) }}">{{ $related->title }}</a></h4>
                     <div class="ratting-price">
                        <div class="ratting">
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                        </div>
                        @if ($related->payment == 1)
                           @php $relOnSale = $related->promotion == 1 && $related->discount < $related->price; @endphp
                           @if ($relOnSale)
                              <div class="rbt-price">
                                 <span class="price">{{ number_format($related->discount, 0, ',', '.') }}</span>
                                 <span class="off-price">${{ number_format($related->price, 0, ',', '.') }}</span>
                              </div>
                           @else
                              <div class="rbt-price">
                                 <span class="price">{{ number_format($related->price, 0, ',', '.') }}</span>
                              </div>
                           @endif
                        @elseif($related->payment == 0)
                           <span class="price">GRATIS</span>
                        @endif

                     </div>
                     <ul class="coach-footer">
                        <li><i class="fas fa-list"></i><span>{{ $related->lessons_count ?? count($related->lessons) }} Clases</span></li>
                        <li><i class="fas fa-folder"></i><span>{{ $related->chapters_count ?? count($related->chapters) }} Temas</span></li>
                     </ul>
                  </div>
               </div>
            </div>
         @endforeach
      </div>
   </div>
</section>
@endif