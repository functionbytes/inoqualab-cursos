@extends('layouts.pages')


@section('content')

<main class="main-area fix">

   <!-- breadcrumb-area-end -->

   <section class="instructors-details-wrapper">

      <div class="container">
         <div class="row">
            <div class="col-xl-12 col-lg-12">
               
                  
                  <div class="container">
                     <div class="row">
                        <div class="col-12">
                           <div class="instructor-details-wrap">
                              <div class="instructor-details-img">
                                 @if($certifier->hasMedia('thumbnail'))
                                    <img src="{{ $certifier->getFirstMediaUrl('thumbnail') }}" alt="{{ $certifier->firstname . ' ' . $certifier->lastname }}">
                                 @else
                                    <img src="/pages/images/certifier/default.jpg" alt="{{ $certifier->firstname . ' ' . $certifier->lastname }}">
                                 @endif
                              </div>
                              <div class="instructor-details-content">
                                 <div class="content-top">
                                    <div class="left-side-content">
                                       <h2 class="title">{{ $certifier->firstname . ' ' . $certifier->lastname}}</h2>
                                       <span>{{ $certifier->profession }}</span>
                                    </div>
                                    <div class="instructor-details-social" aria-hidden="true">
                                       <ul class="list-wrap">
                                          <li><span class="social-icon"><i class="fab fa-facebook-f"></i></span></li>
                                          <li><span class="social-icon"><i class="fab fa-twitter"></i></span></li>
                                          <li><span class="social-icon"><i class="fab fa-whatsapp"></i></span></li>
                                          <li><span class="social-icon"><i class="fab fa-linkedin-in"></i></span></li>
                                          <li><span class="social-icon"><i class="fab fa-youtube"></i></span></li>
                                       </ul>
                                    </div>
                                 </div>
                                 <div class="instructor-info-wrap">
                                    <ul class="list-wrap">
                                       <li>
                                          <div class="rating">
                                             <i class="fas fa-star"></i>
                                             <i class="fas fa-star"></i>
                                             <i class="fas fa-star"></i>
                                             <i class="fas fa-star"></i>
                                             <i class="fas fa-star"></i>
                                          </div>
                                          (5.0 Calificaciones)
                                       </li>
                                      
                                    </ul>
                                 </div>
                                 <div class="bio-content">
                                    @if($certifier->description!=null)
                                    <p class="info">{!! $certifier->description !!}.</p>
                                    @endif
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Coach Section Start -->
               <section class="course-section rel z-1 pt-120 rpt-90 pb-100 rpb-70 ">
                  <div class="container">
                     <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-8 col-md-8">
                           <div class="section-title text-center mb-40">
                              <h2>Cursos dictados</h2>
                           </div>
                        </div>
                     </div>
                     <div class="row course-active justify-content-center">
                        @foreach ($courses as $course)
                        <div class="col-lg-4 col-md-6 item {{ $course->categorie->slug }} ">
                           <div class="course-item wow fadeInUp delay-0-2s">
                              <div class="course-image">
                                 <a href="{{ route('courses.view', [$course->slack]) }}" class="category">{{
                                    $course->categorie->title
                                    }}</a>
                                 @if(count($course->getMedia('thumbnail'))>0)
                                 <img src="{{ $course->getfirstMedia('thumbnail')->getfullUrl() }}"
                                    class="card-img-top rounded-0 object-fit-cover" alt="{{ $course->title }}" height="440"
                                    onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                 @else
                                 <img src="{{ asset('/pages/images/courses/default.jpg') }}"
                                    class="card-img-top rounded-0 object-fit-cover" alt="{{ $course->title }}" height="440">
                                 @endif
                                 </a>
                              </div>
                              <div class="course-content">
                                 <h4><a href="{{ route('courses.view', [$course->slack]) }}">{{ $course->title }}</a></h4>
                                 <div class="ratting-price">
                                    <div class="ratting">
                                       <i class="fas fa-star"></i>
                                       <i class="fas fa-star"></i>
                                       <i class="fas fa-star"></i>
                                       <i class="fas fa-star"></i>
                                       <i class="fas fa-star"></i>
                                    </div>
                                    @if ($course->payment == 1)
                                    @php $certifOnSale = $course->promotion == 1 && $course->discount < $course->price; @endphp
                                    @if ($certifOnSale)
                                    <div class="rbt-price">
                                       <span class="price">${{ number_format($course->discount, 0, ',', '.') }}</span>
                                       <span class="off-price">${{ number_format($course->price, 0, ',', '.') }}</span>
                                    </div>
                                    @else
                                    <div class="rbt-price">
                                       <span class="price">${{ number_format($course->price, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @elseif($course->payment == 0)
                                    <span class="price">GRATIS</span>
                                    @endif
               
                                 </div>
                                 <ul class="course-footer">
                                    <li><i class="fas fa-list"></i><span>{{ $course->lessons->count() }} Clases</span></li>
                                    <li><i class="fas fa-folder"></i><span>{{ $course->chapters->count() }} Temas</span></li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        @endforeach
                     </div>
                  </div>
               </section>
               <!-- Coach Section End -->


            </div>
         </div>
      </div>
   </section>
</main>


@endsection

@push('scripts')

<script type="text/javascript">
   $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
</script>


@endpush