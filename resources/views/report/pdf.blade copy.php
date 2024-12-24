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

    .chart-container {
      width: 49%;
      /* Two graphs per row */
      height: 50%;
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
  <script>
    window.onload = function () {
        // Loop through each node and create a chart for each metric
        @foreach($value as $node => $metrics)
            createChart("speed_{{ $node }}", "Speed Report", @json($metrics['speed']));
            createChart("efficiency_{{ $node }}", "Efficiency Report", @json($metrics['efficiency']));
            createChart("no_of_stoppage_{{ $node }}", "Stoppage Report", @json($metrics['no_of_stoppage']));
            createChart("shift_pick_{{ $node }}", "Total Pick Report", @json($metrics['shift_pick']));
        @endforeach
    };

    function createChart(containerId, title, data) {
      const preName = "{{ $previousLabel }}";
      const curName = "{{ $currentLabel }}";
      const lastWeekDataPoints = data.map(item => ({ label: item.label, y: item.previous }));
      const currentWeekDataPoints = data.map(item => ({ label: item.label, y: item.current }));

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
        data: [
          {
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
      if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
        e.dataSeries.visible = false;
      } else {
        e.dataSeries.visible = true;
      }
      e.chart.render();
    }
  </script>
</body>

</html>
