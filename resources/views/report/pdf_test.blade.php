<!DOCTYPE HTML>
<html>

  <head>
    <style>
      body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
      }

      .page {
        width: 210mm;
        height: 297mm;
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

    <script>
      window.onload = function () {
        // Overriding charts 1 and 2 with charts 3 and 4
        createChart("chartContainer1", "Stoppage Report", [
          { label: "N1M1", lastWeek: 10.46, currentWeek: 5.46 },
          { label: "N1M2", lastWeek: 4.27, currentWeek: 3.27 },
          { label: "N1M3", lastWeek: 3.99, currentWeek: 2.99 },
          { label: "N1M4", lastWeek: 2.45, currentWeek: 1.45 }
        ]);

        createChart("chartContainer2", "Total Pick Report", [
          { label: "N1M1", lastWeek: 100, currentWeek: 90 },
          { label: "N1M2", lastWeek: 150, currentWeek: 130 },
          { label: "N1M3", lastWeek: 80, currentWeek: 75 },
          { label: "N1M4", lastWeek: 70, currentWeek: 65 }
        ]);

        createChart("chartContainer3", "Efficiency Report", [
          { label: "N1M1", lastWeek: 90.21, currentWeek: 80.46 },
          { label: "N1M2", lastWeek: 85.25, currentWeek: 70.27 },
          { label: "N1M3", lastWeek: 70.20, currentWeek: 60.99 },
          { label: "N1M4", lastWeek: 65.77, currentWeek: 50.45 }
        ]);

        createChart("chartContainer4", "Speed Report", [
          { label: "N1M1", lastWeek: 266.21, currentWeek: 10.46 },
          { label: "N1M2", lastWeek: 302.25, currentWeek: 2.27 },
          { label: "N1M3", lastWeek: 157.20, currentWeek: 3.99 },
          { label: "N1M4", lastWeek: 148.77, currentWeek: 4.45 }
        ]);

        createChart("chartContainer5", "Total Pick Report", [
            { label: "N1M1", lastWeek: 100, currentWeek: 90 },
            { label: "N1M2", lastWeek: 150, currentWeek: 130 },
            { label: "N1M3", lastWeek: 80, currentWeek: 75 },
            { label: "N1M4", lastWeek: 70, currentWeek: 65 }
        ]);

        createChart("chartContainer6", "Total Pick Report", [
            { label: "N1M1", lastWeek: 100, currentWeek: 90 },
            { label: "N1M2", lastWeek: 150, currentWeek: 130 },
            { label: "N1M3", lastWeek: 80, currentWeek: 75 },
            { label: "N1M4", lastWeek: 70, currentWeek: 65 }
        ]);
        
        createChart("chartContainer7", "Total Pick Report", [
            { label: "N1M1", lastWeek: 100, currentWeek: 90 },
            { label: "N1M2", lastWeek: 150, currentWeek: 130 },
            { label: "N1M3", lastWeek: 80, currentWeek: 75 },
            { label: "N1M4", lastWeek: 70, currentWeek: 65 }
        ]);

        createChart("chartContainer8", "Total Pick Report", [
            { label: "N1M1", lastWeek: 100, currentWeek: 90 },
            { label: "N1M2", lastWeek: 150, currentWeek: 130 },
            { label: "N1M3", lastWeek: 80, currentWeek: 75 },
            { label: "N1M4", lastWeek: 70, currentWeek: 65 }
        ]);

        setTimeout(() => {
            // generatePDF();
        }, 500);
      };

      function createChart(containerId, title, data) {
        const lastWeekDataPoints = data.map(item => ({ label: item.label, y: item.lastWeek }));
        const currentWeekDataPoints = data.map(item => ({ label: item.label, y: item.currentWeek }));

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
              name: "Last Week 01 Sep 2024 to 07 Sep 2024",
              legendText: "Last Week 01 Sep 2024 to 07 Sep 2024",
              showInLegend: true,
              indexLabel: "{y}",
              dataPoints: lastWeekDataPoints
            },
            {
              type: "column",
              name: "Current Week 01 Sep 2024 to 07 Sep 2024",
              legendText: "Current Week 01 Sep 2024 to 07 Sep 2024",
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

      function generatePDF() {
        const element = document.body;
        const options = {
            margin: [0, 0, 0, 0],
            filename: 'chart-report.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(options).from(element).save();
        }
    </script>
  </head>

  <body>
    <div class="page">
      <div class="title">Node 1</div>
      <div class="charts-wrapper">
        <!-- Chart 1 and 2 are overridden by chart 3 and 4 -->
        <div class="chart-container" id="chartContainer1"></div>
        <div class="chart-container" id="chartContainer2"></div>
        <div class="chart-container" id="chartContainer3"></div>
        <div class="chart-container" id="chartContainer4"></div>
      </div>
    </div>
    <div class="page">
      <div class="title">Node 2</div>
      <div class="charts-wrapper">
        <div class="chart-container" id="chartContainer5"></div>
        <div class="chart-container" id="chartContainer6"></div>
        <div class="chart-container" id="chartContainer7"></div>
        <div class="chart-container" id="chartContainer8"></div>
      </div>
    </div>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <!-- <script src="{{ url('canvasjs.min.js') }}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  </body>

</html>
