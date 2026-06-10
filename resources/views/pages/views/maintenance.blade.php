
@extends('layouts.maintenance')

@section('title', 'Inicio')

@section('content')


    <div class="page-content m-0">
    <div class="container text-center">
        <div class="display-2 mb-5 font-weight-semibold">Maintenance Mode</div>
        <h1 class="h3  mb-3">We will be back soon</h1>
        <p class="h5 font-weight-normal mb-4 leading-normal">Please wait this site is undermaintenance</p>
        <div class="row">
            <div class="col-md-6 d-block mx-auto mb-2">
                <div class="row">
                    <div class="col text-start">
                        <span class="badge badge-danger-light badge-pill">Working</span>
                    </div>
                </div>
                <div class="col col-auto">
                    <div class="fs-18 font-weight-semibold">60%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="progress-wrapper mb-5 col-md-6 d-block mx-auto">
            <div class="progress progress-md">
                <div class="progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
            </div>
        </div>
    </div>
</div>

@endsection
