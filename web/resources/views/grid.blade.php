@extends('layouts.app')

@section('content')
    <div class="container-fluid h-100">
        <div class="row h-100">
            @php $cols = (count($devices) > 0 && !(count($devices) % 3)) ? 'col-md-6 col-xl-4' :'col-lg-6'; @endphp
            @foreach ($devices AS $device)
                <div class="{{$cols}} text-center quickview">
                    <div class="wrapper device" data-device="{{$device->slug}}" data-interval="{{config('sensors.update_interval_secs')}}">
                        <h2>@lang('main.unknown')</h2>
                        <h3>00:00</h3>
                        <h1>0 °</h1>
                        <div class="row">
                            <div class="additionals">
                                <div>
                                    <span class="wi wi-humidity"></span>
                                    <span class="humidity value">-</span>
                                </div>
                                <div>
                                    <span class="wi wi-barometer"></span>
                                    <span class="pressure value">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modals">
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('main.details')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('main.close')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="chart-container" data-translations="{{base64_encode(json_encode(['overview' => __('main.overview'), 'lastUpdate' => __('main.lastUpdate')]))}}"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <span class="type-switch">
                            <button type="button" class="btn btn-dark switch" data-type="temperature"><span class="wi wi-thermometer"></span>@lang('main.temperature')</button>
                            <button type="button" class="btn btn-dark switch" data-type="humidity"><span class="wi wi-humidity"></span>@lang('main.humidity')</button>
                            <button type="button" class="btn btn-dark switch" data-type="pressure"><span class="wi wi-barometer"></span>@lang('main.pressure')</button>
                        </span>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('main.close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection