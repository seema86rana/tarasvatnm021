@php
    $counts = 0;
    if (!function_exists('formatMinutes')) {
        function formatMinutes($minutes) {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return ($hours > 0 ? "{$hours}hr " : "") . ($mins > 0 ? "{$mins}min" : "");
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
        <div class="header">Machine Stop Report: {{ ucwords($filter) }} ({{ $currentDay }})</div>
        <br>
        <hr>
        <br>
        <table width="100%" border="0">
            <thead>
                <tr>
                    <td style="font-size: 16px; padding: 10px; border: none; float: left;">
                        <b>D: {{ $firstRec->device_name }}</b>
                    </td>
                    <td style="font-size: 16px; padding: 10px; border: none; float: right;">
                        <b>{{ $userDetail->name }}</b>
                    </td>
                </tr>
            </thead>
        </table>
        <br>
        <hr>
        <br>
    </div>
    
    @foreach ($reportData as $machine => $shifts)
        @foreach ($shifts as $shiftTime => $shiftDetails)
            <table width="100%" cellpadding="10" cellspacing="0" border="1" style="border-collapse: collapse; margin-bottom: 30px;">
                <thead style="background-color: #eee;">
                    <tr>
                        <td style="font-size: 16px; padding: 10px; border: none; float: left;">
                            <b>{{ $machine }}</b>
                        </td>
                        
                        <td style="font-size: 16px; padding: 10px; border: none; float: left;"></td>

                        <td style="font-size: 16px; padding: 10px; border: none; float: right;">
                            <b>{{ $shiftTime }}</b>
                        </td>
                    </tr>
                    <tr style="background-color: #ddd;">
                        <th>Stop Count</th>
                        <th>Stop Time</th>
                        <th>Stop Duration (hrs/min)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shiftDetails['records'] as $record)
                        <tr>
                            <td>{{ $record['stop_count'] }}</td>
                            <td>{{ $record['stop_time'] }}</td>
                            <td>{{ formatMinutes($record['duration_min']) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #e9dff7; font-weight: bold;">
                        <td colspan="2" align="right"></td>
                        <td>
                            Total Duration: {{ formatMinutes($shiftDetails['total_duration_min']) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endforeach
</body>
</html>