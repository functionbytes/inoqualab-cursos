@extends('layouts.auth')
@section('title', 'Inicio')
@section('content')


    <div class="coming-soon-area">
        <div class="d-table">
            <div class="d-table-cell">
                <div class="coming-soon-content">
                    <a class="logo">
                        <img src="{{ getlogo() }}" alt="{{ setting('page_title') }}">
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
    <script src="{{ asset('pages/js/views/comings.js') }}"></script>
@endpush