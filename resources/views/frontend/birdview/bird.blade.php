
<?php
    $birdHeaderData = [
        'totalGreenEfficiency' => 0,
        'totalYellowEfficiency' => 0,
        'totalOrangeEfficiency' => 0,
        'totalRedEfficiency' => 0,
        'totalDarkEfficiency' => 0,
        'totalMachineRunning' => 0,
        'deviceTime' => date('h:i A'),
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
                return $red;
                // return $dark;
        }
    }

    function dotBackgroundClass($status) {
        // background color class
        $green = 'bg_green';
        $red = 'bg_red';
        $dark = 'bg_dark';

        if($status == 1) {
            return $green;
        } elseif($status == 0) {
            return $red;
        } else {
            return $red;
            // return $dark;
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
            return $close;
            // return $maintainance;
        }
    }

    function dullClass($activeData){
        if($activeData == 0){
          return 'dull-preview';
        }
        return '';
    }
?>


@if(isset($machineData) && count($machineData->toArray()) > 0)
    @php $countActive = 0 @endphp
    <div class="machine_inner_wrapper">
        @foreach($machineData as $mKey => $mValue)
            @php 
                $birdHeaderData['totalMachineEfficiency'] = round($birdHeaderData['totalMachineEfficiency'] + (float)$mValue->efficiency, 2);
                $birdHeaderData['deviceTime'] = date('h:i A', strtotime($mValue->machineMaster->device_datetime ?? date('Y-m-d H:i:s')));
                if($mValue->status == 1) {
                    $birdHeaderData['totalMachineSpeed'] += (float)$mValue->speed;
                    $countActive += 1; 
                }

                $hour = $mValue->last_running / 60;
                $hourR = $hour <= 9 ? ('0'.floor($hour)) : floor($hour);
                $min = $mValue->last_running % 60;
                $minR = $min < 10 ? ('0'.$min) : $min;

                $hour = $mValue->last_stop / 60;
                $hourS = $hour <= 9 ? ('0'.floor($hour)) : floor($hour);
                $min = $mValue->last_stop % 60;
                $minS = $min < 10 ? ('0'.$min) : $min;

                $hour = $mValue->total_running / 60;
                $hourTR = $hour <= 9 ? ('0'.floor($hour)) : floor($hour);
                $min = $mValue->total_running % 60;
                $minTR = $min < 10 ? ('0'.$min) : $min;

                $hour = $mValue->total_time / 60;
                $hourTT = $hour <= 9 ? ('0'.floor($hour)) : floor($hour);
                $min = $mValue->total_time % 60;
                $minTT = $min < 10 ? ('0'.$min) : $min;

                $birdModalData = [
                        'name' => $mValue->machine->name,
                        'backgroundClass' => birdBackgroundClass($mValue->efficiency, $birdHeaderData),
                        'dotBackgroundClass' => dotBackgroundClass($mValue->status),
                        'efficiency' => $mValue->efficiency,
                        'speed' => $mValue->speed,
                        'running' => '- '.$hourR.'h '.$minR.'m',
                        'stop' => '- '.$hourS.'h '.$minS.'m',
                        'total_running' => '- '.$hourTR.'h '.$minTR.'m',
                        'total_time' => '- '.$hourTT.'h '.$minTT.'m',
                        'pickThisShift' => $mValue->pickCal->shift_pick,
                        'pickThisDay' => $mValue->pickCal->total_pick,
                        'stoppage' => $mValue->no_of_stoppage,
                    ];
            @endphp
            <div class="machine_box {{ $birdModalData['backgroundClass'] }} {{ birdBorderClass($mValue->status, $birdHeaderData) }} {{ dullClass($mValue->active_machine) }}" data-id="{{ $mValue->id }}">
                <h6>{{ $mValue->machine->name }}</h6>
                <h4>{{ $mValue->efficiency }} % <span>{{ $mValue->speed <= 9 ? ('0'.(int)$mValue->speed) : (int)$mValue->speed }}</span></h4>
                <input type="hidden" id="birdModalData{{ $mValue->id }}" value="{{ json_encode($birdModalData) }}">
            </div>
        @endforeach
    </div>
    @php 
        $birdHeaderData['averageMachineEfficiency'] += round(($birdHeaderData['totalMachineEfficiency'] / count($machineData->toArray())), 2);
        $birdHeaderData['averageMachineSpeed'] += round((($birdHeaderData['totalMachineSpeed'] > 0 ? $birdHeaderData['totalMachineSpeed'] / $countActive : 0)), 2);
    @endphp
@else
<div class="row justify-content-center" style="display: initial;">
    <div class="col-sm-12 text-center">
        <h1>No Data Found!</h1>
    </div>
</div>
@endif

<div class="header-hidden-data">
    <input type="hidden" id="birdHeaderData" value="{{ json_encode($birdHeaderData) }}">
</div>
