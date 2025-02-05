
@php
    if (!function_exists('colorClass')) {
        function colorClass($value, $total, $type) {
            $green = 'text-green';
            $yellow = 'text-yellow';
            $orange = 'text-orange';
            $red = 'text-red';
            $black = 'text-black';

            $percentage = $type == 1 ? ($value / $total) * 100 : ($value / $total);

            switch (true) {
                case ($percentage >= 90):
                    return $green;
                case ($percentage >= 70 && $percentage <= 90):
                    return $yellow;
                case ($percentage >= 50 && $percentage <= 70):
                    return $orange;
                case ($percentage <= 50):
                    return $red;
                default:
                    return $black;
            }
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Performance Report</title>
    <style>
        .text-green { color: green; }
        .text-yellow { color: yellow; }
        .text-orange { color: orange; }
        .text-red { color: red; }
        .text-black { color: black; }
        @page {
            size: A4 landscape;
            margin: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            background: #d3e3fc;
            padding: 15px;
            font-size: 30px;
            font-weight: bold;
        }
        .report-info {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .table-container {
            page-break-before: always;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th, .td-column {
            background: #ccc;
        }
        .half-row-width {
            width: 50%;
        }
        .btn-container {
            text-align: center;
            margin: 20px;
        }
        .download-btn {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .download-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div style="padding: 20px;" id="report-content">
        <div id="report-data">
            <div class="header">Machine Performance Comparison: {{ ucwords($filter) }} Trends & Insight</div>
            <div class="report-info">
                <div style="display: flex; margin-bottom: 10px;">
                    <span class="half-row-width"><strong>Device Name:</strong> {{ $firstRec->device_name }}</span>
                    <br>
                    <span class="half-row-width"><strong>Report Date:</strong> {{ date('d/m/Y') }}</span>
                </div>
                <span><strong>Username:</strong> {{ $userDetail->name }}</span>
            </div>
        </div>
        @foreach($reportData as $key => $node)
            @php
                $maxCount = 0;
                $maxShift = "";
                $efficiencyCount = $speedCount = $pickCount = $stopageCount = 0;
                foreach ($node['efficiency'] as $shift => $values) {
                    $count = count($values);
                    if ($count > $maxCount) {
                        $maxCount = $count;
                        $maxShift = $shift;
                    }
                }
                $category = array_keys($node['efficiency'][$maxShift]);
            @endphp
            <div class="table-container">
                <h2 style="margin-top: 40px;">Node: {{ $key }}</h2>
                <table>
                    <tr>
                        <th>Category</th>
                        <th>Shift</th>
                        @foreach($category as $value)
                            <th>{{ $value }}</th>
                        @endforeach
                    </tr>
                    @foreach($node['efficiency'] as $key => $efficiency)
                        <tr>
                            @if($efficiencyCount == 0)
                                <td class="td-column">Efficiency</td>
                            @else
                                <td class="td-column"></td>
                            @endif
                            <td class="td-column">{{ $node[$key] }}</td>
                            @foreach($efficiency as $value)
                                <td class="{{ colorClass((float)$value, (int)$node['total_record'][$key], 0) }}">{{ $value }}</td>
                            @endforeach
                        </tr>
                        @php $efficiencyCount++; @endphp
                    @endforeach
                    @foreach($node['speed'] as $key => $speed)
                        <tr>
                            @if($speedCount == 0)
                                <td class="td-column">Speed</td>
                            @else
                                <td class="td-column"></td>
                            @endif
                            <td class="td-column">{{ $node[$key] }}</td>
                            @foreach($speed as $value)
                                <td class="{{ colorClass((float)$value, (int)$node['total_record'][$key], 1) }}">{{ $value }}</td>
                            @endforeach
                        </tr>
                        @php $speedCount++; @endphp
                    @endforeach
                    @foreach($node['pick'] as $key => $pick)
                        <tr>
                            @if($pickCount == 0)
                                <td class="td-column">Pick</td>
                            @else
                                <td class="td-column"></td>
                            @endif
                            <td class="td-column">{{ $node[$key] }}</td>
                            @foreach($pick as $value)
                                <td class="{{ colorClass((float)$value, (int)$node['total_record'][$key], 1) }}">{{ $value }}</td>
                            @endforeach
                        </tr>
                        @php $pickCount++; @endphp
                    @endforeach
                    @foreach($node['stoppage'] as $key => $stoppage)
                        <tr>
                            @if($stopageCount == 0)
                                <td class="td-column">Stoppage</td>
                            @else
                                <td class="td-column"></td>
                            @endif
                            <td class="td-column">{{ $node[$key] }}</td>
                            @foreach($stoppage as $value)
                                <td class="{{ colorClass((float)$value, (int)$node['total_record'][$key], 1) }}">{{ $value }}</td>
                            @endforeach
                        </tr>
                        @php $stopageCount++; @endphp
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>
</body>
</html>