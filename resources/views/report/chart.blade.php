<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ucwords(str_replace("_", " ", config('app.name', 'Laravel'))) }}</title>
        <link rel="icon" href="{{asset('/')}}assets/logo.png">

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <!-- Styles -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }

            .page {
                width: 210mm;
                /* height: 297mm; */
                display: flex;
                flex-direction: column;
                box-sizing: border-box;
                page-break-after: always;
            }

            .title {
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                margin: 20px 0;
            }

            .chart-container {
                width: 48%;
                height: 500px;
                padding: 10px;
                box-sizing: border-box;
                margin: 1%;
                float: left;
                border: 1px solid #ccc;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .charts-wrapper {
                display: flex;
                flex-wrap: wrap;
                width: 100%;
                box-sizing: border-box;
                justify-content: space-between;
            }

            /* Clear the float for the last container */
            .clear {
                clear: both;
            }
        </style>
    </head>

    <body>
        <div class="container justify-content-center" style="display: grid;">
            @foreach($reportData as $node => $shift)
                @foreach($shift as $metrics)
                    <div class="page">
                        <div class="title">{{ $metrics['label'] }}</div>
                        <div class="charts-wrapper">
                            <!-- Chart 1 and 2 are overridden by chart 3 and 4 -->
                            <div class="chart-container" id="speed_{{ str_replace(' ', '', $node) }}"></div>
                            <div class="chart-container" id="efficiency_{{ str_replace(' ', '', $node) }}"></div>
                            <div class="chart-container" id="no_of_stoppage_{{ str_replace(' ', '', $node) }}"></div>
                            <div class="chart-container" id="shift_pick_{{ str_replace(' ', '', $node) }}"></div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script>
            window.onload = function() {
                // Loop through each node and create a chart for each metric
                @foreach($reportData as $node => $shift)
                    @foreach($shift as $metrics)
                        createChart("speed_{{ str_replace(' ', '', $node) }}", "Speed Report", @json($metrics['speed']));
                        createChart("efficiency_{{ str_replace(' ', '', $node) }}", "Efficiency Report", @json($metrics['efficiency']));
                        createChart("no_of_stoppage_{{ str_replace(' ', '', $node) }}", "Stoppage Report", @json($metrics['no_of_stoppage']));
                        createChart("shift_pick_{{ str_replace(' ', '', $node) }}", "Total Pick Report", @json($metrics['shift_pick']));
                    @endforeach
                @endforeach
            };

            function createChart(containerId, title, data) {
                const preName = "{{ $previousLabel }}";
                const curName = "{{ $currentLabel }}";
                const lastWeekDataPoints = data.map(item => ({
                    label: item.label,
                    y: item.previous
                }));
                const currentWeekDataPoints = data.map(item => ({
                    label: item.label,
                    y: item.current
                }));

                var chart = new CanvasJS.Chart(containerId, {
                    animationEnabled: true,
                    title: {
                        text: title
                    },
                    axisY: {
                        title: "",
                        titleFontColor: "#4F81BC",
                        lineColor: "#4F81BC",
                        labelFontColor: "#4F81BC",
                        tickColor: "#4F81BC"
                    },
                    toolTip: {
                        shared: true
                    },
                    legend: {
                        cursor: "pointer",
                        itemclick: toggleDataSeries
                    },
                    data: [{
                            type: "column",
                            name: preName,
                            legendText: preName,
                            showInLegend: true,
                            indexLabel: "{y}",
                            dataPoints: lastWeekDataPoints
                        },
                        {
                            type: "column",
                            name: curName,
                            legendText: curName,
                            showInLegend: true,
                            indexLabel: "{y}",
                            dataPoints: currentWeekDataPoints
                        }
                    ]
                });
                chart.render();
            }

            function toggleDataSeries(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                e.chart.render();
            }

            $(window).on('load', function () {
                $('.canvasjs-chart-credit').remove();
            });
        </script>
    </body>
</html>