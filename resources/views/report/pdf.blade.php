<!DOCTYPE HTML>
<html>

<head>
    <style>
    body {
        width: 210mm;
        /* A4 Width */
        height: 297mm;
        /* A4 Height */
        margin: 0;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    .page {
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
        }

        .page:last-child {
            page-break-after: auto;
        }

    .chart-container {
        width: 47.5%;
        /* Two graphs per row */
        height: 45%;
        /* Two rows */
        box-sizing: border-box;
        padding: 10px;
        border: 2px solid #000;
        /* Border for separation */
        margin: 3px;
    }

    .chart-container div {
        height: 100%;
        width: 100%;
        box-sizing: border-box;
    }
    </style>

<script>
    window.onload = function() {
        // Loop through each node and create a chart for each metric
        @foreach($value as $node => $metrics)
          createChart("speed_{{ $node }}", "Speed Report", @json($metrics['speed']));
          createChart("efficiency_{{ $node }}", "Efficiency Report", @json($metrics['efficiency']));
          createChart("no_of_stoppage_{{ $node }}", "Stoppage Report", @json($metrics['no_of_stoppage']));
          createChart("shift_pick_{{ $node }}", "Total Pick Report", @json($metrics['shift_pick']));
        @endforeach

        setTimeout(() => {
          generatePDF();
        }, 3000);
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

    function generatePDF() {
      const element = document.body;
                const options = {
                    margin: [10, 10, 10, 10],
                    filename: "chart-report.pdf",
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
                };
                html2pdf().set(options).from(element).save();
    }
    </script>
</head>

<body>
    @foreach($value as $node => $metrics)
    <div style="width: 100%;">
        <h2>Name: {{ $metrics['label'] }}</h2>
    </div>
    <div class="chart-container">
        <div id="speed_{{ $node }}"></div>
    </div>
    <div class="chart-container">
        <div id="efficiency_{{ $node }}"></div>
    </div>
    <div class="chart-container">
        <div id="no_of_stoppage_{{ $node }}"></div>
    </div>
    <div class="chart-container">
        <div id="shift_pick_{{ $node }}"></div>
    </div>
    @endforeach

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

</body>

</html>