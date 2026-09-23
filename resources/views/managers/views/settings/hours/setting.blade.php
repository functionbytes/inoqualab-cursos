@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Horario de soporte'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formHours" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.settings.hours.update"),
                      "dashboard" => route("manager.dashboard"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Estado del servicio</h6>
                        <p class="text-muted small mb-0">
                            Habilita o deshabilita el horario de soporte mostrado a los usuarios.</p>
                    </div>

                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="hoursswitch" id="hoursswitch" @if(setting('hoursswitch')=='true' ) checked @endif>
                            <label class="form-check-label fw-semibold" for="hoursswitch">Mostrar horario de soporte</label>
                        </div>
                        <small class="text-muted d-block">Si se deshabilita, el horario de soporte no se muestra a los usuarios.</small>
                    </div>

                    <div id="hoursFields" class="{{ setting('hoursswitch') == 'true' ? '' : 'd-none' }}">

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Horario soporte</h6>
                        <p class="text-muted mb-3">Titulo y subtitulo que se muestran a los usuarios en la seccion de horario de soporte.
                        </p>

                        <div class="row g-3">

                            <div class="col-12">
                                        <label  class="form-label fw-semibold">Titulo</label>
                                        <input type="text" class="form-control" id="hourstitle"  name="hourstitle" value="{{ setting('hourstitle') }}" placeholder="Ingresar titulo">
                            </div>
                            <div class="col-12">
                                        <label  class="form-label fw-semibold">Subtitulo</label>
                                        <input type="text" class="form-control" id="hourssubtitle"  name="hourssubtitle" value="{{ setting('hourssubtitle') }}" placeholder="Ingresar subtitulo">
                            </div>

                        </div>

                    </div>

                    <hr class="my-0">

                    <div class="card-body">

                        <div class="row mt-50">

                            <div class="col-12 ">
                                <div class="mb-4 mt-3">
                                    <div class=" row align-items-center">
                                        <div class=" col-sm-11 ">
                                            <h6 class="fw-bold text-dark mb-1">Horarios</h6>
                                            <p class="text-muted mb-3">Este sera el horario que vera todos los usuarios al momento de solicitar soporte.</p>
                                        </div>
                                    </div>
                                    <div class=" row align-items-center">
                                        <div class="table-responsive table-bussiness-hours">
                                            <table class="table card-table table-vcenter text-nowrap mb-0">
                                                <thead>
                                                <tr class="">
                                                    <th class="w-20 border-bottom-0 ">Dia</th>
                                                    <th class="w-20 border-bottom-0">Estado</th>
                                                    <th class="w-20 border-bottom-0">Hora apertura</th>
                                                    <th class="w-20 border-bottom-0">Hora cierre</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $timestart = ['12:00 AM','12:30 AM','1:00 AM','1:30 AM','2:00 AM','2:30 AM','3:00 AM','3:30 AM','4:00 AM','4:30 AM','5:00 AM','5:30 AM','6:00 AM','6:30 AM','7:00 AM','7:30 AM','8:00 AM','8:30 AM','9:00 AM','9:30 AM','10:00 AM','10:30 AM','11:00 AM','11:30 AM','12:00 PM','12:30 PM','1:00 PM','1:30 PM','2:00 PM','2:30 PM','3:00 PM','3:30 PM','4:00 PM','4:30 PM','5:00 PM','5:30 PM','6:00 PM','6:30 PM','7:00 PM','7:30 PM','8:00 PM','8:30 PM','9:00 PM','9:30 PM','10:00 PM','10:30 PM','11:00 PM','11:30 PM'];
                                                @endphp
                                                <tr class="border-bottom-transparent">
                                                    <td class="">
                                                        <input type="hidden" name="bussinessid1" value="1">
                                                        <select name="bussiness1"
                                                                class="form-control select2 select2-show-search sprukoweeks"
                                                                data-placeholder="Selecionar dia">
                                                            <option label="Selecionar dia"></option>
                                                            <option value="Lunes" {{$bussiness1 !=null ? $bussiness1->weeks == 'Lunes' ? 'selected': '' :''}}>Lunes</option>
                                                            <option value="Martes" {{$bussiness1 !=null ? $bussiness1->weeks == 'Martes' ? 'selected': '' :''}}>Martes</option>
                                                            <option value="Miercoles" {{$bussiness1 !=null ? $bussiness1->weeks == 'Miercoles' ? 'selected': '' :''}}>Miercoles</option>
                                                            <option value="Jueves" {{$bussiness1 !=null ? $bussiness1->weeks == 'Jueves' ? 'selected': '' :''}}>Jueves</option>
                                                            <option value="Viernes" {{$bussiness1 !=null ? $bussiness1->weeks == 'Viernes' ? 'selected': '' :''}}>Viernes</option>
                                                            <option value="Sabado" {{$bussiness1 !=null ? $bussiness1->weeks == 'Sabado' ? 'selected': '' :''}}>Sabado</option>
                                                            <option value="Domingo" {{$bussiness1 !=null ? $bussiness1->weeks == 'Domingo' ? 'selected': '' :''}}>Domingo</option>
                                                        </select>
                                                    </td>
                                                    <td class="">
                                                        <select name="status1" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness1 !=null ? $bussiness1->status == 'Abierto' ? 'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness1 !=null ? $bussiness1->status == 'Cerrado' ? 'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime1" class="form-control select2 select2-show-search sprukostarttime" data-placeholder="Hora apertura">
                                                            <option label="Selecciona hora"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness1 !=null ? $bussiness1->starttime == '24H' ? 'selected' : '' :''}}>24H</option>
                                                            </optgroup>

                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness1 !=null ? $bussiness1->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach

                                                        </select>
                                                    </td>

                                                    <td class="tr_clone">

                                                        <select name="endtime1" class="form-control select2 select2-show-search sprukoendtime" data-placeholder="Hora salida">
                                                            <option label="Seleccionar hora"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness1 !=null ? $bussiness1->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach

                                                        </select>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid2" value="2">
                                                        <input name="bussiness2" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status2" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness2 !=null ? $bussiness2->status == 'Abierto' ? 'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness2 !=null ? $bussiness2->status == 'Cerrado' ? 'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime2"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness2 !=null ? $bussiness2->starttime == '24H' ? 'selected' : '' :''}}>24H</option>

                                                            </optgroup>

                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness2 !=null ? $bussiness2->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach

                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime2"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness2 !=null ? $bussiness2->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid3" value="3">
                                                        <input name="bussiness3" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status3" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness3 !=null ? $bussiness3->status == 'Abierto' ? 'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness3 !=null ? $bussiness3->status == 'Cerrado' ? 'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime3"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness3 !=null ? $bussiness3->starttime == '24H' ? 'selected' : '' :''}}>24H</option>

                                                            </optgroup>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness3 !=null ? $bussiness3->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime3"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness3 !=null ? $bussiness3->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid4" value="4">
                                                        <input name="bussiness4" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status4" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness4 !=null ? $bussiness4->status == 'Abierto' ? 'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness4 !=null ? $bussiness4->status == 'Cerrado' ? 'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime4"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness4 !=null ? $bussiness4->starttime == '24H' ? 'selected' : '' :''}}>24H</option>

                                                            </optgroup>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness4 !=null ? $bussiness4->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime4"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness4 !=null ? $bussiness4->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid5" value="5">
                                                        <input name="bussiness5" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status5" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness5 !=null ? $bussiness5->status == 'Abierto' ?
                                            'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness5 !=null ? $bussiness5->status == 'Cerrado' ?
                                            'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime5"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness5 !=null ? $bussiness5->starttime == '24H' ?
                                                'selected' : '' :''}}>24H</option>

                                                            </optgroup>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness5 !=null ? $bussiness5->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime5"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness5 !=null ? $bussiness5->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid6" value="6">
                                                        <input name="bussiness6" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status6" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness6 !=null ? $bussiness6->status == 'Abierto' ?
                                            'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness6 !=null ? $bussiness6->status == 'Cerrado' ?
                                            'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime6"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness6 !=null ? $bussiness6->starttime == '24H' ?
                                                'selected' : '' :''}}>24H</option>

                                                            </optgroup>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness6 !=null ? $bussiness6->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime6"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness6 !=null ? $bussiness6->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tr_weeks">
                                                        <input type="hidden" name="bussinessid7" value="7">
                                                        <input name="bussiness7" class="form-control sprukoweeks" readonly>

                                                    </td>
                                                    <td class="">
                                                        <select name="status7" class="form-control select2 select2-show-search sprukoopen"
                                                                data-placeholder="Seleccionar estado">
                                                            <option label="Seleccionar estado"></option>
                                                            <option value="Abierto" {{$bussiness7 !=null ? $bussiness7->status == 'Abierto' ?
                                            'selected' :'' :''}}>Abierto</option>
                                                            <option value="Cerrado" {{$bussiness7 !=null ? $bussiness7->status == 'Cerrado' ?
                                            'selected' :'' :''}}>Cerrado</option>
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone1">
                                                        <select name="starttime7"
                                                                class="form-control select2 select2-show-search sprukostarttime"
                                                                data-placeholder="Seleccionar apertura">
                                                            <option label="Seleccionar apertura"></option>
                                                            <optgroup>
                                                                <option value="24H" {{$bussiness7 !=null ? $bussiness7->starttime == '24H' ?
                                                'selected' : '' :''}}>24H</option>

                                                            </optgroup>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness7 !=null ? $bussiness7->starttime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="tr_clone">
                                                        <select name="endtime7"
                                                                class="form-control select2 select2-show-search sprukoendtime"
                                                                data-placeholder="Seleccionar cierre">
                                                            <option label="Seleccionar cierre"></option>
                                                            @foreach($timestart as $time)
                                                                <option value="{{$time}}" {{ $bussiness7 !=null ? $bussiness7->endtime == $time ? 'selected' : '' :''}}>{{$time}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                    </div>{{-- /#hoursFields --}}

                    <div class="card-footer">
                        <button type="submit" id="bussinesshourSubmit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre el horario</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Este horario se muestra a los clientes para indicarles cuándo pueden esperar respuesta del equipo de soporte.</p>

                    <hr class="my-3">

                    <p class="text-muted mb-0"><strong>24H</strong> significa que ese día se atiende las 24 horas, sin hora de cierre.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/hours/setting.js') }}"></script>
@endpush
