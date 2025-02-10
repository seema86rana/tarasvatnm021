@php
    $counts = 0;
    if (!function_exists('colorClass')) {
        function colorClass($pre, $cur) {
            return ($pre >= $cur) ? 'text-green' : 'text-red';
        }
    }
    if (!function_exists('numberFormat')) {
        function numberFormat($number) {
            $number = (int) $number;
            return number_format($number, 0, '', ',');
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
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .page { width: 100%; height: 100%; }
        .header { text-align: center; background: #d3e3fc; padding: 15px; font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .report-info { font-size: 18px; }
        .table-container:nth-of-type(even) { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; }
        .bg-column { background: #ccc; }
        th, td { border: 1px solid #000; padding: 3px; text-align: center; word-wrap: break-word; }
        th { background-color: #ccc; font-weight: bold; }
        .text-green { color: green; }
        .text-red { color: red; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">Machine Performance Comparison: {{ ucwords($filter) }} Trends & Insight</div>
        <div class="report-info">
            <table>
                <tbody>
                    <tr>
                        <td style="border: none; float: inline-start;"><strong>Device Name:</strong> {{ $firstRec->device_name }}</td>
                        <td style="border: none; float: inline-end;"><strong>Report Date:</strong> {{ date('d/m/Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="page">
        @foreach($reportData as $key => $node)
            @php
                $maxShift = array_key_first($node['efficiency']);
                $category = array_keys($node['efficiency'][$maxShift]);
            @endphp
            
            @if($counts % 2 == 0 && $counts != 0)
                </div><div class="page">
            @endif
            
            <div class="table-container">
                <h2>Node: {{ $key }}</h2>
                <table>
                    <tr>
                        <th>Category</th>
                        <th>Shift</th>
                        @foreach($category as $value)
                            <th>{{ $value }}</th>
                        @endforeach
                    </tr>
                    
                    @foreach(['efficiency', 'speed', 'pick', 'stoppage'] as $section)
                        @foreach($node[$section] as $shift => $values)
                            <tr>
                                @if($loop->first)
                                    <td class="bg-column">{{ ucfirst($section) }}</td>
                                @else
                                    <td class="bg-column"></td>
                                @endif
                                <td class="bg-column">{{ $node[$shift] }}</td> <!-- Fixed shift name -->
                                @php $index = 0; @endphp
                                @foreach($values as $value)
                                    @php
                                        $total = isset($node['total_record'][$shift]) ? max(floatval($node['total_record'][$shift]), 1) : 1;

                                        $cur = floatval($value);
                                        if(in_array($section, ['efficiency', 'speed'])) {
                                            $cur = round(floatval($value) / $total, 2);
                                        }

                                        if (($index % 2) == 0) {
                                            $prev = (float)$values[$category[$index]];
                                            $next = (float)$values[$category[$index + 1]];
                                        }
                                        else {
                                            $prev = (float)$values[$category[$index]];
                                            $next = (float)$values[$category[$index - 1]];
                                        }
                                    @endphp
                                    <td class="{{ colorClass($prev, $next) }}">@if($section == 'pick') {{ numberFormat($cur) }} @else {{ $cur }} @endif @if($section == 'efficiency') % @endif</td>
                                    @php $index++; @endphp
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
            
            @php $counts++; @endphp
        @endforeach
    </div>
</body>
</html>