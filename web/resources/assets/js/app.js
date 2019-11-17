require('./bootstrap');
var Highcharts = require('highcharts');
require('bootstrap-select');
global.moment = require('moment');
require('tempusdominus-bootstrap-4');


$(function() {
    Engine.init();
});

var Engine = {

    devices: {},
    charts: {},
    selection: {},

    init: function () {
        this.observe();
        $(".wrapper.device").each(function() {
            var device = $(this);
            Engine.devices[device.data('device')] = {
                object: device,
                interval: device.data('interval'),
                counter: device.data('interval')
            };
        });

        this.datetimepickerConfigure();

        this.update();
    },

    observe: function() {
        $('.quickview').click(function(e) {
            e.preventDefault();
            var device = $(this).find('.device').data('device');
            if (device.length) {
                Engine.detailsLoad(device);
            }
        });

        $('#detailsModal .selectpicker').change(function(e) {
            e.preventDefault();
            Engine.chartSwitch($(this));
        });

        $('#detailsModal .selectpicker').change(function(e) {
            e.preventDefault();
            Engine.chartSwitch($(this));
        });

        $('#detailsModal select.date_group').change(function(e) {
            e.preventDefault();
            Engine.selection['group'] = $(this).val().trim();
        });

        $('#detailsModal button.btn.update').click(function(e) {
            e.preventDefault();
            var device = $('#detailsModal').attr('data-current-device');
            if (device.length) {
                Engine.detailsLoad(device);
            }
        });
    },

    dateSelectionHandle: function(ident, date) {
        date = date.trim();
        Engine.selection[ident] = date;
    },

    update: function () {

        $.each(Engine.devices, function(slug,object) {
            object.counter += 1;

            if (object.counter >= object.interval) {
                object.counter = 0;
                $.ajax({
                    type: "POST",
                    url: "/api/read/",
                    data: {
                        device: slug
                    },
                    success: function(res) {
                        if ('status' in res && res.status == 'OK') {
                            if ('data' in res) {
                                Engine.redraw(slug, res.data);
                            }
                        }
                    },
                    dataType: "json"
                });
            }
        });

        //don't wait for ajax, just start ticking...
        window.setTimeout(function() {
            Engine.update();
        },  1000);
    },

    redraw: function(slug, data) {
        if (!slug in Engine.devices) {
            return false;
        }

        var device = Engine.devices[slug];

        //Update name & time
        if ('device' in data) {
            device.object.find("h2").html(data.device.name);
            device.object.find("h3").html(data.device.updated.time);
        }

        if ('sensors' in data) {
            $.each(data.sensors, function(id, sensor) {
                //Update values
                if ('type' in sensor) {
                    var selector = '';
                    var string = sensor.value + ' ' + sensor.type.unit;
                    switch (sensor.type.slug) {

                        case 'temperature':
                            selector = 'h1';
                            break;

                        case 'humidity':
                            selector = '.humidity.value';
                            break;

                        case 'pressure':
                            selector = '.pressure.value';
                            break;

                        case 'battery':
                            selector = '.battery.value';
                            break;
                    }

                    var sensorContainer = device.object.find(selector);
                    var parentContainer = sensorContainer.parent();
                    if (parentContainer.hasClass('d-none')) {
                        parentContainer.removeClass('d-none');
                    }
                    sensorContainer.html(string);
                }
            });
        }
    },

    detailsLoad: function(device) {
        Engine.overlayLoading(true);
        $.ajax({
            type: "POST",
            url: "/api/details/",
            data: {
                device: device,
                selection: Engine.selection
            },
            success: function(res) {
                if ('status' in res && res.status == 'OK') {
                    if ('data' in res) {
                        Engine.chartDraw(device, res.data);
                        Engine.overlayLoading(false);
                    }
                }
            },
            dataType: "json"
        });

        $("#detailsModal .type-switch button").each(function() {
            $(this).removeClass('d-none');
        });
    },

    overlayLoading: function(enable) {
        var elem = $('#detailsModal .modal-content .loading-overlay');
        if (typeof enable === 'undefined' || enable == false) {
            return elem.removeClass('active');
        }

        return elem.addClass('active');
    },

    chartDraw: function(device, res) {

        //Write selected dates in header
        if ('selection' in res) {
            Engine.datesSet(res.selection);
            Engine.groupingSet(res.selection);
        }

        if ('datasets' in res) {
            Engine.charts = res.datasets;
            var data = res.datasets;
            Engine.dropdownConfigure(data);
            Engine.chartPlot(data[Object.keys(data)[0]]);
            var elem = $('#detailsModal');
            elem.attr('data-current-device', device);
            elem.modal('show');
        }
    },

    chartSwitch: function(elem) {
        var charts = Engine.charts;
        var type = elem.val();

        if (type in charts) {
            Engine.chartPlot(charts[type]);
        }
    },

    chartPlot: function(data) {
        var id = 'chart-container';
        var translations = $('#' + id).data('translations');

        try {
            translations = atob(translations);
            translations = JSON.parse(translations);
        } catch(e) {
            alert("Argh. Cannot parse translations");
            return false;
        }

        Highcharts.chart(id, {
            chart: {
                type: 'spline'
            },
            title: {
                text: translations.overview + ' (' + data.name + ')'
            },
            subtitle: {
                text: translations.lastUpdate + ': ' + data.latest_date
            },
            xAxis: {
                categories: data.labels
            },
            yAxis: {
                title: {
                    text: data.type
                },
                labels: {
                    formatter: function () {
                        return this.value + data.unit;
                    }
                }
            },
            tooltip: {
                crosshairs: true,
                shared: true
            },
            plotOptions: {
                spline: {
                    marker: {
                        radius: 4,
                        lineColor: '#666666',
                        lineWidth: 1
                    }
                }
            },
            series: [{
                name: data.type,
                marker: {
                    symbol: 'square'
                },
                data: data.values
            }]
        });
    },

    datetimepickerConfigure: function () {
        $.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
            icons: {
                date: 'icon-calendar',
                up: 'icon-chevron-up',
                down: 'icon-chevron-down',
                previous: 'icon-chevron-left',
                next: 'icon-chevron-right',
                clear: 'icon-trash',
                close: 'icon-folder-close'
            } });
        $(".input-group.date.datetimepicker").each(function() {
            var format = 'YYYY-MM-DD';
            var picker = $(this);
            if (picker.data('format')) {
                format = picker.data('format');
            }
            var options  = {
                format: format,
                maxDate: moment()
            };
            picker.datetimepicker(options);
            picker.on("hide.datetimepicker", function (e) {
                var elem = $(this).find('input.datetimepicker-input');
                Engine.dateSelectionHandle(elem.attr('name'), moment(e.date).format('YYYY-MM-DD'));
            });
        });

    },

    datesSet: function(selection) {
        //Write datetime dates into header
        if ('datetime' in selection) {
            if ($('#headingSelection h5 a').length) {
                $('#headingSelection h5 a').html(selection.datetime.start_date + ' - ' + selection.datetime.end_date);
            }
        }

        //Write dates into inputs
        if('date' in selection) {
            if ($('#dateStartInput').length && typeof selection.date.start_date !== 'undefined') {
                $('#dateStartInput').val(selection.date.start_date);
            }

            if ($('#dateEndInput').length && typeof selection.date.end_date !== 'undefined') {
                $('#dateEndInput').val(selection.date.end_date);
            }
        }
    },

    groupingSet: function(selection) {
        if ('group' in selection) {
            $('#detailsModal select.date_group').val(selection.group);
        }
    },

    dropdownConfigure: function(data) {
        var selectpicker = $('#detailsModal .dropdown select.selectpicker');
        selectpicker.children('option').each(function() {
            $(this).removeAttr("style");
        });

        var keySelect = Object.keys(data)[0];

        selectpicker.children('option').each( function() {
            var elem = $(this);
            if (typeof data[elem.val()] == 'undefined') {
                elem.css("display","none");
            }

            //Set to datasets first entry
            if (keySelect == elem.val()) {
                selectpicker.val(keySelect);
            }
        });

        selectpicker.selectpicker('refresh');
    }
};