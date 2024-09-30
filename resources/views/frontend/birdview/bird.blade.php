
<?php
    $birdHeaderData = [
        'totalGreenEfficiency' => 0,
        'totalYellowEfficiency' => 0,
        'totalOrangeEfficiency' => 0,
        'totalRedEfficiency' => 0,
        'totalDarkEfficiency' => 0,
        'totalMachineRunning' => 0,
        'totalMachineStop' => 0,
        'totalMachineMaintainance' => 0,
        'totalMachineEfficiency' => 0,
        'averageMachineEfficiency' => 0,
        'totalMachineSpeed' => 0,
        'averageMachineSpeed' => 0,
    ];

    function birdBackgroundClass($efficiency, &$birdHeaderData) {
        // background color class
        $green = 'bg_light_green';
        $yellow = 'bg_light_yellow';
        $orange = 'bg_light_orange';
        $red = 'bg_light_red';
        $dark = 'bg_light_dark';

        switch ($efficiency) {
            case ($efficiency >= 90):
                $birdHeaderData['totalGreenEfficiency'] += 1;
                return $green;
            case ($efficiency >= 70 && $efficiency <= 90):
                $birdHeaderData['totalYellowEfficiency'] += 1;
                return $yellow;
            case ($efficiency >= 50 && $efficiency <= 70):
                $birdHeaderData['totalOrangeEfficiency'] += 1;
                return $orange;
            case ($efficiency <= 50):
                $birdHeaderData['totalRedEfficiency'] += 1;
                return $red;
            default:
                $birdHeaderData['totalDarkEfficiency'] += 1;
                return $dark;
        }
    }

    function dotBackgroundClass($efficiency) {
        // background color class
        $green = 'bg_green';
        $yellow = 'bg_yellow';
        $orange = 'bg_orange';
        $red = 'bg_red';
        $dark = 'bg_dark';

        switch ($efficiency) {
            case ($efficiency >= 90):
                return $green;
            case ($efficiency >= 70 && $efficiency <= 90):
                return $yellow;
            case ($efficiency >= 50 && $efficiency <= 70):
                return $orange;
            case ($efficiency <= 50):
                return $red;
            default:
                return $dark;
        }
    }

    function birdBorderClass($status, &$birdHeaderData) {
        // border class
        $close = 'machine_close';
        $maintainance = 'machine_maintainance';

        if($status == 1) {
            $birdHeaderData['totalMachineRunning'] += 1;
            return '';
        } elseif($status == 0) {
            $birdHeaderData['totalMachineStop'] += 1;
            return $close;
        } else {
            $birdHeaderData['totalMachineMaintainance'] += 1;
            return $maintainance;
        }
    }
?>


@if(isset($machineData) && count($machineData->toArray()) > 0)
    @foreach($machineData as $mKey => $mValue)
        @php 
            $birdHeaderData['totalMachineEfficiency'] = round($birdHeaderData['totalMachineEfficiency'] + (float)$mValue->efficiency, 2);
            $birdHeaderData['totalMachineSpeed'] += (float)$mValue->speed;

            $hour = $mValue->last_running / 60;
            $hourR = $hour <= 9 ? ('0'.round($hour)) : round($hour);
            $min = $mValue->last_running % 60;
            $minR = $min < 10 ? ('0'.$min) : $min;
            $hour = $mValue->last_stop / 60;
            $hourS = $hour <= 9 ? ('0'.round($hour)) : round($hour);
            $min = $mValue->last_stop % 60;
            $minS = $min < 10 ? ('0'.$min) : $min;

            $birdModalData = [
                    'name' => $mValue->machineMaster->machine_display_name,
                    'backgroundClass' => birdBackgroundClass($mValue->efficiency, $birdHeaderData),
                    'dotBackgroundClass' => dotBackgroundClass($mValue->efficiency),
                    'efficiency' => $mValue->efficiency,
                    'speed' => $mValue->speed,
                    'running' => '- '.$hourR.'h '.$minR.'m',
                    'stop' => '- '.$hourS.'h '.$minS.'m',
                    'pickThisShift' => $mValue->total_pick_shift_wise,
                    'pickThisDay' => $mValue->total_pick,
                    'stoppage' => $mValue->no_of_stoppage,
                ];
        @endphp
        <div class="machine_box {{ $birdModalData['backgroundClass'] }} {{ birdBorderClass($mValue->status, $birdHeaderData) }}" data-id="{{ $mValue->id }}">
            <h6>{{ $mValue->machineMaster->machine_display_name }}</h6>
            <h4>{{ $mValue->efficiency }} % <span>{{ $mValue->last_running <= 9 ? ('0'.(int)$mValue->last_running) : (int)$mValue->last_running }}</span></h4>
            <input type="hidden" id="birdModalData{{ $mValue->id }}" value="{{ json_encode($birdModalData) }}">
        </div>
    @endforeach
    @php 
        $birdHeaderData['averageMachineEfficiency'] += round(($birdHeaderData['totalMachineEfficiency'] / count($machineData->toArray())), 2);
        $birdHeaderData['averageMachineSpeed'] += round(($birdHeaderData['totalMachineSpeed'] / count($machineData->toArray())), 2);
    @endphp
@else
<div class="row justify-content-center">
    <div class="col-sm-12 p-1 text-center">
        <h1>No Data Found!</h1>
    </div>
</div>
@endif

<div class="header-hidden-data">
    <input type="hidden" id="birdHeaderData" value="{{ json_encode($birdHeaderData) }}">
</div>
