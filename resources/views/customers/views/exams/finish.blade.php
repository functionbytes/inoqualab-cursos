@extends('layouts.customers')

@section('title', $course->title )

@section('head')
    @php $url = URL::current(); @endphp
    <meta name="title" content="{{ $course->title }}">
    <meta name="description" content="{{ $course->short_detail }} ">
    <meta property="og:title" content="{{ $course->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}">
@endpush

@section('content')

@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    <div class="aula-result">
                        @include('customers.partials.views.exams.exam-result')
                    </div>
                </div>
            </main>
        </div>
    </div>
@else
    <div class="aula">
        <div class="aula-grid">
            <div class="aula-result">
                @include('customers.partials.views.exams.exam-result')
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif

@push('css')
<style>
    .ar-rate { margin-top: 28px; padding-top: 24px; border-top: 1px solid #e7ecf1; }
    .ar-rate-title { font-size: 16px; font-weight: 700; margin: 0 0 14px; color: #1b2a3a; }
    .ar-stars { display: inline-flex; gap: 6px; margin-bottom: 14px; }
    .ar-stars .star { background: none; border: none; cursor: pointer; font-size: 30px; line-height: 1; color: #f5b740; padding: 2px; }
    .ar-stars .star i { transition: transform .1s ease; }
    .ar-stars .star:hover i { transform: scale(1.15); }
    .ar-comment { width: 100%; max-width: 460px; border: 1.5px solid #e7ecf1; border-radius: 10px; padding: 12px 14px; font-size: 14px; font-family: inherit; resize: vertical; display: block; margin: 0 auto 14px; }
    .ar-comment:focus { outline: none; border-color: #008bcd; box-shadow: 0 0 0 3px rgba(0,139,205,.12); }
    .ar-rate-done { font-size: 15px; font-weight: 700; color: #006fa3; display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .ar-rate-stars { color: #f5b740; font-size: 18px; }
    .ar-primary:disabled { opacity: .5; cursor: not-allowed; }
    .ar-foot.lv-foot { padding: 26px 0 0; margin-top: 6px; border-top: 1px solid var(--line); }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var wrap = document.getElementById('rateStars');
        if (!wrap) return;
        var stars  = wrap.querySelectorAll('.star');
        var input  = document.getElementById('rateValue');
        var submit = document.getElementById('rateSubmit');

        function paint(val) {
            stars.forEach(function (s) {
                var v = parseInt(s.getAttribute('data-val'), 10);
                s.querySelector('i').className = 'fa-solid fa-star' + (v <= val ? ' is-filled' : '');
            });
        }
        stars.forEach(function (s) {
            var v = parseInt(s.getAttribute('data-val'), 10);
            s.addEventListener('mouseenter', function () { paint(v); });
            s.addEventListener('click', function () {
                input.value = v;
                paint(v);
                if (submit) submit.disabled = false;
            });
        });
        wrap.addEventListener('mouseleave', function () { paint(parseInt(input.value, 10) || 0); });
    })();
</script>
@endpush
@endsection
