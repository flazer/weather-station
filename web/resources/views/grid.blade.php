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
                                <div class="d-none">
                                    <span class="wi wi-humidity"></span>
                                    <span class="humidity value">-</span>
                                </div>
                                <div class="d-none">
                                    <span class="wi wi-barometer"></span>
                                    <span class="pressure value">-</span>
                                </div>
                                <div class="d-none">
                                    <span class="fas fa-battery-full"></span>
                                    <span class="battery value">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modals">
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" data-current-device="" aria-labelledby="detailsModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="loading-overlay">
                        <div class="ani">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('main.details')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('main.close')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="accordion">
                            <div class="card">
                                <div class="card-header" id="headingSelection">
                                    <h5 class="mb-0">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseDateSelection" aria-expanded="false" class="collapsed">
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapseDateSelection" class="collapse" aria-labelledby="headingSelection" data-parent="#accordion">
                                    <div class="card-body justify-content-between">
                                        <div class="form-group">
                                            <label for="dateStartInput">@lang('main.start')</label>
                                            <div class="input-group date datetimepicker" id="datetimepicker_start" data-format="{{config('sensors.format_datepicker')}}" data-target-input="nearest">
                                                <input id="dateStartInput" type="text" class="form-control datetimepicker-input" name="date_start" data-target="#datetimepicker_start"/>
                                                <div class="input-group-append" data-target="#datetimepicker_start" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="dateEndInput">@lang('main.end')</label>
                                            <div class="input-group date datetimepicker" id="datetimepicker_end" data-format="{{config('sensors.format_datepicker')}}" data-target-input="nearest">
                                                <input id="dateEndInput" type="text" class="form-control datetimepicker-input" name="date_end" data-target="#datetimepicker_end"/>
                                                <div class="input-group-append" data-target="#datetimepicker_end" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="dateGroupSelect">@lang('main.grouping')</label>
                                            <br>
                                            <select id="dateGroupSelect" class="form-control date_group">
                                                <option value="">---</option>
                                                @foreach (config('sensors.groups') AS $group)
                                                    <option value="{{$group}}">@lang('main.' . $group)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary update" data-toggle="collapse" data-target="#collapseDateSelection">@lang('main.update')</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="chart-container" data-translations="{{base64_encode(json_encode(['overview' => __('main.overview'), 'lastUpdate' => __('main.lastUpdate')]))}}"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <select class="selectpicker" data-style="btn-dark">
                            <option data-icon="wi wi-thermometer" value="temperature">@lang('main.temperature')</option>
                            <option data-icon="wi wi-humidity" value="humidity">@lang('main.humidity')</option>
                            <option data-icon="wi wi-barometer" value="pressure">@lang('main.pressure')</option>
                            <option data-icon="fas fa-battery-full" value="battery">@lang('main.battery')</option>
                        </select>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('main.close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection