@php
    $kickerIcon = ['video'=>'circle-play','audio'=>'audio','image'=>'image','pdf'=>'pdf','zip'=>'zip'][$typeSlug] ?? 'book';
    $kickerLabel = ['video'=>'Video','audio'=>'Audio','image'=>'Imagen','pdf'=>'Documento','zip'=>'Recurso'][$typeSlug] ?? 'Lección';
    $hasResource = in_array($typeSlug, ['audio','image','pdf','zip']) && $classing->hasMedia($typeSlug);
    $tabId = 'lesson'.$classing->id;
@endphp

@if ($typeSlug == 'video' && $classing->url)
    <div class="lp-video">
        <span class="lp-tag">@include('customers.includes.icon', ['name' => 'circle-play']) Clase en video</span>
        <iframe
            src="{{ route('customers.courses.player', ['lesson' => $classing->id]) }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            allowfullscreen></iframe>
    </div>
@elseif ($typeSlug == 'pdf' && $classing->hasMedia('pdf'))
    <div class="lp-embed lp-embed-pdf">
        <span class="lp-tag light">@include('customers.includes.icon', ['name' => 'pdf']) Documento PDF</span>
        <iframe src="{{ $classing->getFirstMedia('pdf')->getFullUrl() }}" title="{{ $classing->title }}"></iframe>
    </div>
@elseif ($typeSlug == 'image' && $classing->hasMedia('image'))
    <div class="lp-embed lp-embed-image">
        <img src="{{ $classing->getFirstMedia('image')->getFullUrl() }}" alt="{{ $classing->title }}">
    </div>
@elseif ($typeSlug == 'audio' && $classing->hasMedia('audio'))
    <div class="lp-embed lp-embed-audio">
        <span class="lp-tag">@include('customers.includes.icon', ['name' => 'audio']) Clase en audio</span>
        <audio controls preload="metadata" src="{{ $classing->getFirstMedia('audio')->getFullUrl() }}"></audio>
    </div>
@endif

<div class="lv-lhead">
    <div>
        <span class="lk">@include('customers.includes.icon', ['name' => $kickerIcon]) {{ $kickerLabel }}</span>
        <h1>{{ ucfirst($classing->title) }}</h1>
    </div>
    {{-- Solo visible en mobile (ver .lv-rail-toggle en aula.css): abre el
         rail de capítulos/lecciones, que en ese breakpoint queda oculto
         fuera de pantalla (position:fixed; translateX(-105%)) sin ninguna
         otra forma de acceder a él. --}}
    <button type="button" class="lv-rail-toggle" aria-label="Ver clases del curso" aria-expanded="false" aria-controls="lvRail">
        @include('customers.includes.icon', ['name' => 'menu'])
    </button>
</div>

<ul class="nav nav-pills lv-tabs px-0" id="{{ $tabId }}-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="lv-tab active" id="{{ $tabId }}-desc-tab" data-bs-toggle="pill" data-bs-target="#{{ $tabId }}-desc" type="button" role="tab">Descripción</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="lv-tab" id="{{ $tabId }}-res-tab" data-bs-toggle="pill" data-bs-target="#{{ $tabId }}-res" type="button" role="tab">Recursos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="lv-tab" id="{{ $tabId }}-notes-tab" data-bs-toggle="pill" data-bs-target="#{{ $tabId }}-notes" type="button" role="tab">Notas</button>
    </li>
</ul>

<div class="tab-content" id="{{ $tabId }}-tabContent">
    <div class="tab-pane fade show active" id="{{ $tabId }}-desc" role="tabpanel">
        <div class="lv-pane">
            @if ($classing->detail)
                {!! clean($classing->detail, 'content') !!}
            @else
                <p>Esta lección no tiene una descripción adicional.</p>
            @endif
        </div>
    </div>
    <div class="tab-pane fade" id="{{ $tabId }}-res" role="tabpanel">
        <div class="lv-pane">
            @if ($hasResource)
                @php
                    $fileIcon = ['audio'=>'audio','image'=>'image','pdf'=>'pdf','zip'=>'zip'][$typeSlug] ?? 'file';
                    $fileMedia = $classing->getFirstMedia($typeSlug);
                @endphp
                <div class="lv-reslist">
                    <a class="lv-res" href="{{ $fileMedia->getFullUrl() }}" target="_blank">
                        <span class="ic">@include('customers.includes.icon', ['name' => $fileIcon])</span>
                        <span class="info">
                            <b>{{ $fileMedia->file_name }}</b>
                            <span>Material de la lección · {{ strtoupper($typeSlug) }}</span>
                        </span>
                        <span class="dl">@include('customers.includes.icon', ['name' => 'download'])</span>
                    </a>
                </div>
            @else
                <p>Esta lección no tiene recursos adicionales para descargar.</p>
            @endif
        </div>
    </div>
    <div class="tab-pane fade" id="{{ $tabId }}-notes" role="tabpanel">
        <div class="lv-pane">
            <textarea class="lv-notes" data-lesson-notes="{{ $classing->id }}" placeholder="Escribe tus notas de esta clase…"></textarea>
            <div class="lv-notes-foot" data-default="Tus notas se guardan automáticamente en este dispositivo.">@include('customers.includes.icon', ['name' => 'circle-check']) <span class="txt">Tus notas se guardan automáticamente en este dispositivo.</span></div>
        </div>
    </div>
</div>

<div class="lv-foot">
    <form action="{{ route('customers.courses.prev') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
        @csrf
        <input type="hidden" name="course" value="{{ $course->id }}">
        <input type="hidden" name="lesson" value="{{ $classing->id }}">
        <input type="hidden" name="user" value="{{ $user->id }}">
        @if ($prevLesson && $prevLesson !== 'true')
            <button type="submit" class="lv-fbtn" aria-label="Lección anterior">
                @include('customers.includes.icon', ['name' => 'arrow-left'])
                <span class="fb-txt">
                    <span class="l">Anterior</span>
                    <span class="t">{{ ucfirst(Str::lower($prevLesson->title)) }}</span>
                </span>
            </button>
        @else
            <button type="submit" class="lv-fbtn" aria-label="Volver al curso">
                @include('customers.includes.icon', ['name' => 'arrow-left'])
                <span class="fb-txt"><span class="l">Volver al curso</span></span>
            </button>
        @endif
    </form>

    <form action="{{ route('customers.courses.realized') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
        @csrf
        <input type="hidden" name="course" value="{{ $course->id }}">
        <input type="hidden" name="lesson" value="{{ $classing->id }}">
        <input type="hidden" name="user" value="{{ $user->id }}">
        @if ($nextLesson && $nextLesson !== 'true')
            <button type="submit" class="lv-fbtn next" aria-label="Completar y seguir">
                <span class="fb-txt"><span class="l">Siguiente</span><span class="t">Completar y continuar</span></span>
                @include('customers.includes.icon', ['name' => 'arrow-right'])
            </button>
        @else
            <button type="submit" class="lv-fbtn next" aria-label="Completar y presentar examen">
                <span class="fb-txt"><span class="l">Finalizar</span><span class="t">Presentar examen</span></span>
                @include('customers.includes.icon', ['name' => 'arrow-right'])
            </button>
        @endif
    </form>
</div>

@push('css')
<style>
    .lv-foot form { display: contents; }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        var $notes = $('.lv-notes[data-lesson-notes="{{ $classing->id }}"]');
        if ($notes.length) {
            var key = 'lesson-notes-{{ $classing->id }}';
            var $foot = $notes.closest('.lv-pane').find('.lv-notes-foot');
            var $footTxt = $foot.find('.txt');
            var defaultTxt = $foot.data('default');
            var saveTimer = null;

            try { $notes.val(localStorage.getItem(key) || ''); } catch (e) {}

            $notes.on('input', function () {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(function () {
                    try { localStorage.setItem(key, $notes.val()); } catch (e) {}
                    $foot.addClass('saved');
                    $footTxt.text('Guardado');
                    setTimeout(function () {
                        $foot.removeClass('saved');
                        $footTxt.text(defaultTxt);
                    }, 1600);
                }, 500);
            });
        }
    });
</script>
@endpush
