

            <div class="courses-topbar">
               <div class="row">
                  <div class="col-lg-9 col-md-9">
                     <div class="topbar-result-count">
                        <p>Resultados 1 – 6 of {{ count($courses) }}</p>
                     </div>
                  </div>
                  <div class="col-lg-3 col-md-3">
                     <div class="topbar-ordering-and-search">
                        <div class="row align-items-right">
                              <div class="topbar-ordering">
                                 <select class="selects">
                                     <option> Ordenar por visto </option>
                                     <option> Ordenar por fecha </option>
                                     <option> Ordenar por a - z  </option>
                                     <option> Ordenar por z - a </option>
                                 </select>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="row">
               @foreach($courses as $course)

                 

                  <div class="col-lg-6 col-md-6 ">
                     <div class="single-courses-box mb-30">
                        <div class="courses-image">
                           <a href="{{ route('courses.view', array($course->slack)) }}" class="d-block">
                            
                              @if($course->thumbnail!=null)
                                    <img src="{{ asset('/pages/img/courses/'.$course->thumbnail) }}" alt="image" loading="lazy">
                              @else
                                    <img src="{{ asset('/pages/img/courses/default.jpg') }}" alt="image" loading="lazy">
                              @endif
                           </a>
                           <div class="courses-tag">
                              <a class="d-block">{{ $course->categorie->title }}</a>
                           </div>
                        </div>
                        <div class="courses-content">
                           <h3>
                              <a href="{{ route('courses.view', array($course->slack)) }}" class="d-inline-block">
                                 {{ $course->title }}
                              </a>
                           </h3>
                           <p> {{substr(strip_tags($course->short_detail), 0, 400)}}</p>
                           <div class="courses-rating">
                              <div class="review-stars-rated">
                                 <i class='bx bxs-star'></i>
                                 <i class='bx bxs-star'></i>
                                 <i class='bx bxs-star'></i>
                                 <i class='bx bxs-star'></i>
                                 <i class='bx bxs-star'></i>
                              </div>
                              <div class="rating-total">
                                 5.0 (1 rating)
                              </div>
                           </div>
                        </div>
                        <div class="courses-box-footer">
                           <ul>
                              <li class="courses-lesson">
                                 <i class='bx bx-book-open'></i>
                                 {{ $course->lessons->count() }} Clases
                              </li>
                              <li class="courses-price">
                                @if ($course->payment == 1)
                                    @php $itemOnSale = $course->promotion == 1 && $course->discount < $course->price; @endphp
                                    @if ($itemOnSale)
                                        <div class="content-promotion">
                                            <div class="promotion-promotion">
                                                ${{ number_format($course->discount) }}</div>
                                            <div class="promotion-price">${{ number_format($course->price) }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="content-promotion">
                                            <div class="course-price">${{ number_format($course->price) }}
                                            </div>
                                        </div>
                                    @endif
                                @elseif($course->payment == 0)
                                    <div class="content-promotion">
                                        <div class="course-price">GRATIS</div>
                                    </div>
                                @endif
                            </li>
                           </ul>
                        </div>
                        <div class="courses-box-action">
                            @if($course->payment == 0)
                                <form class="form-add-to-cart" action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="course">
                                    <input type="hidden" name="slack" value="{{ $course->slack }}">
                                    <input type="hidden" name="buy_now" value="1">
                                    <button type="submit" class="cba-btn cba-free">
                                        <i class="fas fa-graduation-cap"></i> Obtener gratis
                                    </button>
                                </form>
                            @else
                                <form class="form-add-to-cart" action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="course">
                                    <input type="hidden" name="slack" value="{{ $course->slack }}">
                                    <button type="submit" class="cba-btn">
                                        <i class="fas fa-cart-plus"></i> Agregar al carrito
                                    </button>
                                </form>
                            @endif
                        </div>
                     </div>
                  </div>
               @endforeach
            </div>
