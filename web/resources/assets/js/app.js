require('./bootstrap');
var Highcharts = require('highcharts');


$(function() {
    Engine.init();
});

var Engine = {

    devices: {},
    charts: {},

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

        this.update();
    },

    observe: function() {
        $('.quickview').click(function(e) {
            e.preventDefault();
            Engine.loadDetails($(this));
        });

        $('#detailsModal .switch').click(function(e) {
            e.preventDefault();
            Engine.switchChart($(this));
        });
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
                    }

                    device.object.find(selector).html(string);
                }
            });
        }
    },

    loadDetails: function(elem) {
        var device = elem.find('.device').data('device');
        $.ajax({
            type: "POST",
            url: "/api/details/",
            data: {
                device: device
            },
            success: function(res) {
                if ('status' in res && res.status == 'OK') {
                    if ('data' in res) {
                        Engine.chartDraw(device, res.data);
                    }
                }
            },
            dataType: "json"
        });

        $("#detailsModal .type-switch button").each(function() {
            $(this).removeClass('d-none');
        });
    },

    chartDraw: function(device, data) {
        Engine.charts = data;

        $("#detailsModal .type-switch button").each(function() {
            var elem = $(this);
            if (typeof data[elem.data('type')] == 'undefined') {
                elem.addClass('d-none');
            }
        });

        Engine.plotChart(data[Object.keys(data)[0]]);
        $('#detailsModal').modal('show');
    },

    switchChart: function(elem) {
        var charts = Engine.charts;
        var type = elem.data('type');

        if (type in charts) {
            Engine.plotChart(charts[type]);
        }
    },

    plotChart: function(data) {
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
    }
};