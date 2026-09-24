@php $defaultImage = asset('/pages/images/courses/default.jpg'); @endphp
@if ($course->getFirstMedia('thumbnail'))
    <img src="{{ $course->cardImageUrl() }}" alt="{{ $course->title }}" loading="lazy"
         class="js-img-fallback" data-fallback-src="{{ $defaultImage }}">
@else
    <img src="{{ $defaultImage }}" alt="{{ $course->title }}" loading="lazy">
@endif
@if ($withBadge ?? true)
    <span class="crs-card-badge {{ $card['isFree'] ? 'is-free' : '' }}">{{ $card['isFree'] ? 'Gratis' : 'Premium' }}</span>
@endif
