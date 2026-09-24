{{--
    Sub-navegación entre las secciones de gestión de un curso: Temas, Clases,
    Anuncios, Quiz, Examen. Se incluye al inicio de @section('content') en
    cada una de esas 5 vistas para poder saltar de una a otra sin volver al
    hub (managers.views.courses.courses.navegation) y re-navegar.

    Props:
    - course (Course, requerido)
    - active (string, requerido) — uno de: chapters, lessons, announcements, quiz, exam
--}}
<div class="course-subnav mb-3">
    <div class="course-subnav-top">
        <a href="{{ route('manager.courses.navegation', $course->slack) }}" class="course-subnav-back" title="Volver al curso" aria-label="Volver al curso">
            {!! \App\Html\IconHelper::render('arrow-left', 15) !!}
        </a>
        <div class="course-subnav-title text-truncate">{{ $course->title }}</div>
    </div>
    <div class="course-subnav-segmented">
        <a href="{{ route('manager.courses.chapters', $course->slack) }}" class="course-subnav-seg {{ ($active ?? '') === 'chapters' ? 'active' : '' }}">
            {!! \App\Html\IconHelper::render('list', 15) !!}
            <span>Temas</span>
        </a>
        <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="course-subnav-seg {{ ($active ?? '') === 'lessons' ? 'active' : '' }}">
            {!! \App\Html\IconHelper::render('play-circle', 15) !!}
            <span>Clases</span>
        </a>
        <a href="{{ route('manager.courses.announcements', $course->slack) }}" class="course-subnav-seg {{ ($active ?? '') === 'announcements' ? 'active' : '' }}">
            {!! \App\Html\IconHelper::render('megaphone', 15) !!}
            <span>Anuncios</span>
        </a>
        <a href="{{ route('manager.courses.quiz', $course->slack) }}" class="course-subnav-seg {{ ($active ?? '') === 'quiz' ? 'active' : '' }}">
            {!! \App\Html\IconHelper::render('check-circle', 15) !!}
            <span>Quiz</span>
        </a>
        <a href="{{ route('manager.courses.exam', $course->slack) }}" class="course-subnav-seg {{ ($active ?? '') === 'exam' ? 'active' : '' }}">
            {!! \App\Html\IconHelper::render('file-text', 15) !!}
            <span>Examen</span>
        </a>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('managers/css/includes/course-subnav.css') }}?v={{ @filemtime(public_path('managers/css/includes/course-subnav.css')) ?: 1 }}">
