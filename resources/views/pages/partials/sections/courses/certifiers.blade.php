@if (count($certifiers) > 0)
    <div class="widget widget-menu wow fadeInUp delay-0-4s">
        <h4 class="widget-title">Instructores</h4>
        <ul>
            @foreach ($certifiers as $certifier)
                <li><a href="{{ route('courses.certifiers', [$certifier->firsname]) }}">{{ $certifier->firsname }} </a> <span>({{ count($certifier->firsname) }})</span></li>
            @endforeach
        </ul>
    </div>
@endif