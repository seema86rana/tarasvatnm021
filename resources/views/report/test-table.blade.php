<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .page {
            width: 100%;
            height: 100%;
        }
        .header {
            text-align: center;
            background: #d3e3fc;
            padding: 15px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-info {
            font-size: 18px;
        }
        .table-container:nth-of-type(even) {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .td-column {
			background: #ccc;
		}
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            word-wrap: break-word;
        }
        th {
            background-color: #ccc;
            font-weight: bold;
        }
        .text-green { color: green; }
        .text-red { color: red; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">Machine Performance Comparison: Weekly Trends & Insight</div>
        <div class="report-info">
            <table>
                <tbody>
                    <tr>
                        <td style="border: none; float: inline-start;"><span class="half-row-width"><strong>Device Name:</strong> 58.bf.25.23.3f.68</span></td>
                        <td style="border: none; float: inline-end;"><span class="half-row-width"><strong>Report Date:</strong> 09/02/2025</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
    <div class="page">
        <div class="table-container">
            <h2>Node: N1</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Shift</th>
                    <th>N1:M1 (Last)</th>
                    <th>N1:M1 (Current)</th>
                    <th>N1:M2 (Last)</th>
                    <th>N1:M2 (Current)</th>
                    <th>N1:M3 (Last)</th>
                    <th>N1:M3 (Current)</th>
                    <th>N1:M4 (Last)</th>
                    <th>N1:M4 (Current)</th>
                </tr>
                <tr>
                    <td class="td-column">Efficiency</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">54.48%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">30.36%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">76.75%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">54.48%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">31.94%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">25.26%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">31.94%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-red">42.11%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">15.79%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">42.11%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column">Speed</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">420</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">400</td>
                    <td class="text-red">0</td>
                    <td class="text-green">460</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-green">230</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Pick</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">24600</td>
                    <td class="text-red">0</td>
                    <td class="text-green">23950</td>
                    <td class="text-red">0</td>
                    <td class="text-green">28100</td>
                    <td class="text-red">0</td>
                    <td class="text-green">28200</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">17550</td>
                    <td class="text-red">0</td>
                    <td class="text-green">17200</td>
                    <td class="text-red">0</td>
                    <td class="text-green">20550</td>
                    <td class="text-red">0</td>
                    <td class="text-green">20600</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">7300</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7000</td>
                    <td class="text-red">0</td>
                    <td class="text-green">8500</td>
                    <td class="text-red">0</td>
                    <td class="text-green">8450</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Stoppage</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">339</td>
                    <td class="text-red">0</td>
                    <td class="text-green">298</td>
                    <td class="text-red">0</td>
                    <td class="text-green">258</td>
                    <td class="text-red">0</td>
                    <td class="text-green">182</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">255</td>
                    <td class="text-red">0</td>
                    <td class="text-green">224</td>
                    <td class="text-red">0</td>
                    <td class="text-green">194</td>
                    <td class="text-red">0</td>
                    <td class="text-green">134</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">104</td>
                    <td class="text-red">0</td>
                    <td class="text-green">92</td>
                    <td class="text-red">0</td>
                    <td class="text-green">81</td>
                    <td class="text-red">0</td>
                    <td class="text-green">57</td>
                    <td class="text-red">0</td>
                </tr>
            </table>
        </div>
        <div class="table-container">
            <h2>Node: N2</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Shift</th>
                    <th>N2:M1 (Last)</th>
                    <th>N2:M1 (Current)</th>
                    <th>N2:M2 (Last)</th>
                    <th>N2:M2 (Current)</th>
                    <th>N2:M3 (Last)</th>
                    <th>N2:M3 (Current)</th>
                    <th>N2:M4 (Last)</th>
                    <th>N2:M4 (Current)</th>
                </tr>
                <tr>
                    <td class="td-column">Efficiency</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">30.36%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">78.03%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">51.7%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">10.86%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">25.26%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">31.94%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">1.88%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-red">15.79%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">42.11%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column">Speed</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">450</td>
                    <td class="text-red">0</td>
                    <td class="text-green">400</td>
                    <td class="text-red">0</td>
                    <td class="text-green">230</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">210</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Pick</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">29000</td>
                    <td class="text-red">0</td>
                    <td class="text-green">26700</td>
                    <td class="text-red">0</td>
                    <td class="text-green">24500</td>
                    <td class="text-red">0</td>
                    <td class="text-green">23950</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">20850</td>
                    <td class="text-red">0</td>
                    <td class="text-green">19050</td>
                    <td class="text-red">0</td>
                    <td class="text-green">17600</td>
                    <td class="text-red">0</td>
                    <td class="text-green">17200</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">8650</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7900</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7250</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7000</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Stoppage</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">176</td>
                    <td class="text-red">0</td>
                    <td class="text-green">217</td>
                    <td class="text-red">0</td>
                    <td class="text-green">220</td>
                    <td class="text-red">0</td>
                    <td class="text-green">216</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">129</td>
                    <td class="text-red">0</td>
                    <td class="text-green">164</td>
                    <td class="text-red">0</td>
                    <td class="text-green">164</td>
                    <td class="text-red">0</td>
                    <td class="text-green">165</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">53</td>
                    <td class="text-red">0</td>
                    <td class="text-green">68</td>
                    <td class="text-red">0</td>
                    <td class="text-green">69</td>
                    <td class="text-red">0</td>
                    <td class="text-green">69</td>
                    <td class="text-red">0</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="page">
        <div class="table-container">
            <h2>Node: N3</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Shift</th>
                    <th>N2:M1 (Last)</th>
                    <th>N2:M1 (Current)</th>
                    <th>N2:M2 (Last)</th>
                    <th>N2:M2 (Current)</th>
                    <th>N2:M3 (Last)</th>
                    <th>N2:M3 (Current)</th>
                    <th>N2:M4 (Last)</th>
                    <th>N2:M4 (Current)</th>
                </tr>
                <tr>
                    <td class="td-column">Efficiency</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">30.36%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">78.03%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">51.7%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">10.86%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">25.26%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">31.94%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">1.88%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-red">15.79%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">42.11%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column">Speed</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">450</td>
                    <td class="text-red">0</td>
                    <td class="text-green">400</td>
                    <td class="text-red">0</td>
                    <td class="text-green">230</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">210</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Pick</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">29000</td>
                    <td class="text-red">0</td>
                    <td class="text-green">26700</td>
                    <td class="text-red">0</td>
                    <td class="text-green">24500</td>
                    <td class="text-red">0</td>
                    <td class="text-green">23950</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">20850</td>
                    <td class="text-red">0</td>
                    <td class="text-green">19050</td>
                    <td class="text-red">0</td>
                    <td class="text-green">17600</td>
                    <td class="text-red">0</td>
                    <td class="text-green">17200</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">8650</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7900</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7250</td>
                    <td class="text-red">0</td>
                    <td class="text-green">7000</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Stoppage</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">176</td>
                    <td class="text-red">0</td>
                    <td class="text-green">217</td>
                    <td class="text-red">0</td>
                    <td class="text-green">220</td>
                    <td class="text-red">0</td>
                    <td class="text-green">216</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">129</td>
                    <td class="text-red">0</td>
                    <td class="text-green">164</td>
                    <td class="text-red">0</td>
                    <td class="text-green">164</td>
                    <td class="text-red">0</td>
                    <td class="text-green">165</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">53</td>
                    <td class="text-red">0</td>
                    <td class="text-green">68</td>
                    <td class="text-red">0</td>
                    <td class="text-green">69</td>
                    <td class="text-red">0</td>
                    <td class="text-green">69</td>
                    <td class="text-red">0</td>
                </tr>
            </table>
        </div>
        <div class="table-container">
            <h2>Node: N4</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Shift</th>
                    <th>N3:M1 (Last)</th>
                    <th>N3:M1 (Current)</th>
                    <th>N3:M2 (Last)</th>
                    <th>N3:M2 (Current)</th>
                    <th>N3:M3 (Last)</th>
                    <th>N3:M3 (Current)</th>
                    <th>N3:M4 (Last)</th>
                    <th>N3:M4 (Current)</th>
                </tr>
                <tr>
                    <td class="td-column">Efficiency</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-red">77.18%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">75.68%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">73.97%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">10%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">55.32%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">1.88%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-orange">57.89%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                    <td class="text-red">0%</td>
                </tr>
                <tr>
                    <td class="td-column">Speed</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">210</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">400</td>
                    <td class="text-red">0</td>
                    <td class="text-green">230</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">210</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-green">230</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">210</td>
                    <td class="text-red">0</td>
                    <td class="text-green">225</td>
                    <td class="text-red">0</td>
                    <td class="text-green">200</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Pick</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">28350</td>
                    <td class="text-red">0</td>
                    <td class="text-green">27750</td>
                    <td class="text-red">0</td>
                    <td class="text-green">34950</td>
                    <td class="text-red">0</td>
                    <td class="text-green">16950</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">20550</td>
                    <td class="text-red">0</td>
                    <td class="text-green">19750</td>
                    <td class="text-red">0</td>
                    <td class="text-green">25750</td>
                    <td class="text-red">0</td>
                    <td class="text-green">12000</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">8500</td>
                    <td class="text-red">0</td>
                    <td class="text-green">8300</td>
                    <td class="text-red">0</td>
                    <td class="text-green">10700</td>
                    <td class="text-red">0</td>
                    <td class="text-green">4800</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column">Stoppage</td>
                    <td class="td-column">12:30 PM - 08:30 PM</td>
                    <td class="text-green">218</td>
                    <td class="text-red">0</td>
                    <td class="text-green">159</td>
                    <td class="text-red">0</td>
                    <td class="text-green">161</td>
                    <td class="text-red">0</td>
                    <td class="text-green">201</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">08:30 PM - 04:30 AM</td>
                    <td class="text-green">164</td>
                    <td class="text-red">0</td>
                    <td class="text-green">121</td>
                    <td class="text-red">0</td>
                    <td class="text-green">121</td>
                    <td class="text-red">0</td>
                    <td class="text-green">150</td>
                    <td class="text-red">0</td>
                </tr>
                <tr>
                    <td class="td-column"></td>
                    <td class="td-column">04:30 AM - 12:30 PM</td>
                    <td class="text-green">68</td>
                    <td class="text-red">0</td>
                    <td class="text-green">48</td>
                    <td class="text-red">0</td>
                    <td class="text-green">48</td>
                    <td class="text-red">0</td>
                    <td class="text-green">61</td>
                    <td class="text-red">0</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
