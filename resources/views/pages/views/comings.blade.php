@extends('layouts.auth')
@section('title', 'Inicio')
@section('content')


    <div class="coming-soon-area">
        <div class="d-table">
            <div class="d-table-cell">
                <div class="coming-soon-content">
                    <a class="logo">
                        <img src="/pages/images/logo/logo.svg" alt="image">
                    </a>
                    <h2>Nosotros estamos lanzando pronto</h2>
                    <div id="timer" class="flex-wrap d-flex justify-content-center">
                        <div id="days" class="align-items-center flex-column d-flex justify-content-center"></div>
                        <div id="hours" class="align-items-center flex-column d-flex justify-content-center"></div>
                        <div id="minutes" class="align-items-center flex-column d-flex justify-content-center"></div>
                        <div id="seconds" class="align-items-center flex-column d-flex justify-content-center"></div>
                    </div>
                    <form class="newsletter-form" data-bs-toggle="validator">
                        <div class="form-group">
                            <input type="email" class="input-newsletter" placeholder="Introduce tu correo electrónico" name="EMAIL"
                                required autocomplete="off">
                            <span class="label-title"><i class='bx bx-envelope'></i></span>
                        </div>
                        <button type="submit" class="default-btn"><i
                                class='bx bx-paper-plane icon-arrow before'></i><span
                                class="label">Suscribir</span><i
                                class="bx bx-paper-plane icon-arrow after"></i></button>
                        <div id="validator-newsletter" class="form-result"></div>
                        
                    </form>
                </div>
            </div>
        </div>

    @endsection




@push('scripts')




    <script type="text/javascript">

      function makeTimer() {
            var endTime = new Date("December 20, 2021 17:00:00 PDT");
            var endTime = (Date.parse(endTime)) / 1000;
            var now = new Date();
            var now = (Date.parse(now) / 1000);
            var timeLeft = endTime - now;
            var days = Math.floor(timeLeft / 86400);
            var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
            var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600)) / 60);
            var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));
            if (hours < "10") { hours = "0" + hours; }
            if (minutes < "10") { minutes = "0" + minutes; }
            if (seconds < "10") { seconds = "0" + seconds; }
            $("#days").html(days + "<span>Dias</span>");
            $("#hours").html(hours + "<span>Horas</span>");
            $("#minutes").html(minutes + "<span>Minutos</span>");
            $("#seconds").html(seconds + "<span>Segundos</span>");
    }

    setInterval(function() { makeTimer(); }, 0);

    </script>

@endpush