@extends('layouts.managers')

@section('content')

    <div class="page-content-wrapper ">
       
        <div class="content ">

            
            <div class=" container-fluid   container-fixed-lg">


                <div id="rootwizard" class="m-t-50">
                    <div class="tab-content">

                        <div class="pane padding-20 sm-no-padding">

                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('manager.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('manager.courses') }}">Cursos</a>
                                </li>
                                <li class="breadcrumb-item active">Duplicar
                                </li>
                            </ul>


                            <div class="row row-same-height">
                                <div class="col-md-12">
                                    <div class="padding-30 sm-padding-5">

                                        {!! Form::open(['route' => ['manager.courses.action'], 'method' => 'POST', 'files' => true, 'enctype' => 'multipart/form-data']) !!}
                                        {{ csrf_field() }}

                                        <input name="course" type="hidden" value="{{ $course->slack }}">

                                        <div class="form-group-attached">
                                            <div class="row clearfix">
                                                <div class="col-sm-12">
                                                    <div class="form-group form-group-default disabled">
                                                        <label>CURSO</label>
                                                        {!! Form::text('courses', $course->title, ['class' => 'form-control' . ($errors->has('courses') ? ' is-invalid' : ''), 'disabled']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row clearfix">
                                                <div class="col-sm-12">
                                                    <div class="form-group form-group-default ">
                                                        <label>DUPLICAR</label>
                                                        {!! Form::text('duplicate', null, ['class' => 'form-control' . ($errors->has('courses') ? ' is-invalid' : '')]) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row m-t-25">
                                            <div class="col-xl-12">
                                                {!! Form::submit(__('Duplicar'), ['class' => 'btn btn-primary pull-right btn-lg btn-block']) !!}
                                            </div>
                                        </div>

                                    </div>

                                    {!! Form::close() !!}

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
    </div>
    
    </div>


@endsection

