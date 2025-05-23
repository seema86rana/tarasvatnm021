<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\MachineMasterLog;
use App\Models\MachineMaster;
use App\Models\MachineStatus;
use Illuminate\Bus\Queueable;
use App\Models\PickCalculation;
use App\Models\MachineStatusLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;

class ProcessPacket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reqData;
    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $reqData)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->reqData = $reqData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->reqData['Did'] && $this->reqData['Tnd']) {
                
                $this->processData($this->reqData);
                Log::info("Packet processed successfully: " . json_encode($this->reqData));
            } 
            else {
                Log::warning('Invalid data in request: ' . json_encode($this->reqData));
            }
        } catch (Exception $e) {
            Log::error('Packet processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processData(array $reqData)
    {
        $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
        $shifts = json_decode($device->shift, true);
        $lastShift = end($shifts);

        $totalNode  = $reqData['Tnd'];
        $staticTime = $lastShift['shift_end_time'];

        $shiftDate          = '';
        $shiftName          = '';
        $shiftStartDatetime = "";
        $shiftEndDatetime   = "";
        $shiftDateType1     = "";
        $shiftDateType2     = "";

        $utcDeviceDatetime = Carbon::createFromFormat('Ymd H:i:s', $reqData['Ddt'], env('DEVICE_TIMEZONE', 'UTC'));
        $deviceDatetime = $utcDeviceDatetime->setTimezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');
        $deviceDate = date('Y-m-d', strtotime($deviceDatetime));

        if (strtotime("{$deviceDate} {$staticTime}") < strtotime($deviceDatetime)) {
            $shiftDateType1 = $deviceDate;
            $shiftDateType2 = date('Y-m-d', strtotime('+1 day', strtotime($deviceDate)));
        } else {
            $shiftDateType1 = date('Y-m-d', strtotime('-1 day', strtotime($deviceDate)));
            $shiftDateType2 = $deviceDate;
        }

        foreach ($shifts as $shift) {
            $shiftStart = date('Y-m-d H:i:s', strtotime(($shift['shift_start_day'] == 1 ? $shiftDateType1 : $shiftDateType2) . " {$shift['shift_start_time']}"));
            $shiftEnd = date('Y-m-d H:i:s', strtotime(($shift['shift_end_day'] == 1 ? $shiftDateType1 : $shiftDateType2) . " {$shift['shift_end_time']}"));
        
            if (strtotime($deviceDatetime) >= strtotime($shiftStart) && strtotime($deviceDatetime) < strtotime($shiftEnd)) {
                $shiftDate = $shiftDateType1;
                $shiftName = $shift['shift_name'];
                $shiftStartDatetime = $shiftStart;
                $shiftEndDatetime = $shiftEnd;
                break;
            }
        }

        if (empty($shiftStartDatetime) || empty($shiftEndDatetime)) {
            Log::error("Error: Shift Start Date and Shift End Date are empty: devive Name: {$reqData['Did']}, device datetime: {$deviceDatetime}");
            throw new Exception("Shift Start Date and Shift End Date are empty: devive Name: {$reqData['Did']}, device datetime: {$deviceDatetime}");
        }

        $insertedMachineIds = [];
        $machineIds = MachineStatus::whereDate('shift_date', $shiftDate)
                            ->where('shift_start_datetime', $shiftStartDatetime)
                            ->where('shift_end_datetime', $shiftEndDatetime)
                            ->whereHas('machine.node.device', function ($query) use ($device) {
                                $query->where('device_id', $device->id);
                            })
                            ->pluck('machine_id')->toArray();

        if (isset($reqData['Nd']) && is_array($reqData['Nd']) && count($reqData['Nd']) > 0) {

            foreach ($reqData['Nd'] as $node) {
                if (empty($node['Nid'])) {
                    continue;
                }
                $nodeName = 'N' . $node['Nid'];
                $nodeMasterTable = NodeMaster::updateOrCreate(
                    ['device_id' => $device->id, 'name' => $nodeName],
                    []
                );
                
                if (!isset($node['Md']) || !is_array($node['Md']) || count($node['Md']) < 1) {
                    continue;
                }

                foreach ($node['Md'] as $machine) {
                    if (empty($machine['Mid']) || empty($machine['Mdt'])) {
                        continue;
                    }
                    $utcMachineDatetime = Carbon::createFromFormat('Ymd H:i:s', $machine['Mdt'], env('DEVICE_TIMEZONE', 'UTC'));
                    $machineDatetime = $utcMachineDatetime->setTimezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');

                    $machineName = "{$nodeName}:M{$machine['Mid']}";
                    $machineMasterTable = MachineMaster::updateOrCreate(
                        ['node_id' => $nodeMasterTable->id, 'name' => $machineName],
                        [
                            'current_status' => 1,
                        ]
                    );

                    $machineLogData = [
                        'machine_id' => $machineMasterTable->id,
                        'speed' => $machine['Spd'] ?? '',
                        'mode' => $machine['St'] ?? 0,
                        'pick' => $machine['Tp'] ?? '',
                        'machine_datetime' => $machineDatetime,
                    ];
                    $machineLogTable = MachineMasterLog::create($machineLogData);

                    $machineStatusTable = MachineStatus::where('machine_id', $machineMasterTable->id)
                                        ->whereDate('shift_date', $shiftDate)
                                        ->where('shift_start_datetime', $shiftStartDatetime)
                                        ->where('shift_end_datetime', $shiftEndDatetime)
                                        ->first();

                    $diffMinLastStop     = 0;
                    $diffMinLastRunning  = 0;
                    $diffMinTotalRunning = 0;
                    $pickResponse        = [];
                    $deviceTime          = Carbon::parse($deviceDatetime);
                    $machineTime         = Carbon::parse($machineDatetime);
                    $shiftStartTime      = Carbon::parse($shiftStartDatetime);
                    
                    $machineStatusData = [
                        'machine_id' => $machineMasterTable->id,
                        'speed' => (int) $machine['Spd'],
                        'status' => (int) $machine['St'] ?? 0,
                        'total_time' => $shiftStartTime->diffInSeconds($deviceTime),
                        'device_datetime' => $deviceDatetime,
                        'machine_datetime' => $machineDatetime,
                        'shift_date' => $shiftDate,
                        'shift_name' => $shiftName,
                        'shift_start_datetime' => $shiftStartDatetime,
                        'shift_end_datetime' => $shiftEndDatetime,
                    ];

                    if ($machineStatusTable) {

                        $lastRecTime = Carbon::parse($machineStatusTable->device_datetime);
                        $pickResponse = $this->pickCalculation((int)$machine['Tp'], $machineStatusTable->id, 'update');

                        $diffMinLastStop = $machineStatusTable->last_stop ?? 0;
                        $diffMinLastRunning = $machineStatusTable->last_running ?? 0;
                        $diffMinTotalRunning = $machineStatusTable->total_running ?? 0;

                        if ($machine['St'] == 1 && $machineStatusTable->status == 1) {
                            $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                            $diffMinLastStop = $diffMinLastStop;
                            $diffMinLastRunning = $machineTime->diffInSeconds($deviceTime);
                            $diffMinTotalRunning += $lastRecTime->diffInSeconds($deviceTime);
                        }
                        else if ($machine['St'] == 1 && $machineStatusTable->status == 0) {
                            $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                            $diffMinLastStop += ($shiftStartTime > $machineTime ? 0 : $lastRecTime->diffInSeconds($machineTime));
                            $diffMinLastRunning = $machineTime->diffInSeconds($deviceTime);
                            $diffMinTotalRunning += ($shiftStartTime > $machineTime ? $shiftStartTime->diffInSeconds($deviceTime) : $machineTime->diffInSeconds($deviceTime));

                            $machineMasterTable->stop_alert_sent = 0;
                            $machineMasterTable->save();
                        }
                        else if ($machine['St'] == 0 && $machineStatusTable->status == 1) {
                            $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage + 1;
                            $diffMinLastStop = $machineTime->diffInSeconds($deviceTime);
                            $diffMinLastRunning += ($shiftStartTime > $machineTime ? 0 : $lastRecTime->diffInSeconds($machineTime));
                            $diffMinTotalRunning += ($shiftStartTime > $machineTime ? 0 : $lastRecTime->diffInSeconds($machineTime));

                            $machineMasterTable->stop_alert_sent = 0;
                            $machineMasterTable->save();
                        }
                        else if ($machine['St'] == 0 && $machineStatusTable->status == 0) {
                            $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                            $diffMinLastStop = $machineTime->diffInSeconds($deviceTime);
                            $diffMinLastRunning = $diffMinLastRunning;
                            $diffMinTotalRunning = $diffMinTotalRunning;
                        }
                        else {
                            $machineStatusData['no_of_stoppage'] = 0;
                            $diffMinLastStop = 0;
                            $diffMinLastRunning = 0;
                            $diffMinTotalRunning = 0;
                        }
                    } 
                    else {
                        $machineStatusTablePrevious = MachineStatus::where('machine_id', $machineMasterTable->id)->orderBy('id', 'desc')->first();

                        if ($machineStatusTablePrevious) {
                            $pickResponse = $this->pickCalculation((int)$machine['Tp'], $machineStatusTablePrevious->id, 'insert');
                        } else {
                            $pickResponse = $this->pickCalculation((int)$machine['Tp'], NULL, 'insert');
                        } 

                        if ($machine['St'] == 1) {
                            $machineStatusData['no_of_stoppage'] = 0;
                            $diffMinLastStop = $shiftStartTime->diffInSeconds($machineTime);
                            $diffMinLastRunning = $machineTime->diffInSeconds($deviceTime);
                        }
                        else if ($machine['St'] == 0) {
                            $machineStatusData['no_of_stoppage'] = 1;
                            $diffMinLastStop = $machineTime->diffInSeconds($deviceTime);
                            $diffMinLastRunning = $shiftStartTime->diffInSeconds($machineTime);
                        }
                        else {
                            $machineStatusData['no_of_stoppage'] = 0;
                            $diffMinLastStop = 0;
                            $diffMinLastRunning = 0;
                        }

                        if ($machine['St'] == 1) {
                            $diffMinTotalRunning = $shiftStartTime->diffInSeconds($deviceTime);
                        }
                        else if ($machine['St'] == 0) {
                            $diff = $shiftStartTime->diff($machineTime);
                            $diffMinTotalRunning = $shiftStartTime > $machineTime ? 0 : (($diff->h * 60) + $diff->i) * 60;
                        }
                        else {
                            $diffMinTotalRunning = 0;
                        }

                        // if($diffMinLastStop > 0) {
                        //     $machineStatusData['no_of_stoppage'] = 1;
                        // }
                    }

                    $machineStatusData['efficiency']    = 0;
                    $machineStatusData['last_stop']     = $diffMinLastStop;
                    $machineStatusData['last_running']  = $diffMinLastRunning;
                    $machineStatusData['total_running'] = $diffMinTotalRunning;

                    if($machineStatusData['total_running'] != 0 && $machineStatusData['total_time'] != 0) {
                        $machineStatusData['efficiency'] = round((($machineStatusData['total_running'] / $machineStatusData['total_time']) * 100), 2);
                    }

                    if ($machineStatusTable) {
                        MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);

                        if($pickResponse['status']) {
                            if ($pickResponse['isUpdate'] && $pickResponse['id']) {
                                $pickData = [
                                    'machine_status_id' => $machineStatusTable->id,
                                ];
                                PickCalculation::where('id', $pickResponse['id'])->update($pickData);
                            }
                        }

                        $insertedMachineIds[] = $machineMasterTable->id;
                        
                        //-----------------------------------------------------------------
                        $machineStatusData['machine_status_id'] = $machineStatusTable->id;
                        $machineStatusData['machine_log_id'] = $machineLogTable->id;
                        MachineStatusLog::create($machineStatusData);
                        //-----------------------------------------------------------------
                    } 
                    else {
                        $insertMachineStatus = MachineStatus::create($machineStatusData);

                        if($pickResponse['status']) {
                            if ($pickResponse['isUpdate'] && $pickResponse['id']) {
                                $pickData = [
                                    'machine_status_id' => $insertMachineStatus->id,
                                ];
                                PickCalculation::where('id', $pickResponse['id'])->update($pickData);
                            }
                        }

                        //-----------------------------------------------------------------
                        $machineStatusData['machine_status_id'] = $insertMachineStatus->id;
                        $machineStatusData['machine_log_id'] = $machineLogTable->id;
                        MachineStatusLog::create($machineStatusData);
                        //-----------------------------------------------------------------
                    }
                }
            }
        }

        if (count($insertedMachineIds) > 0) {
            $inactivatedMachineIds = array_diff($insertedMachineIds, $machineIds);
            MachineMaster::whereIn('id', $inactivatedMachineIds)->update([
                    'current_status' => 0,
                ]);

            Log::info("These machine status ids are inactivated: " . implode(',', $inactivatedMachineIds));
        }
        
        Log::info("Processing data Total Node -> {$totalNode}");
        return true;
    }

    protected function pickCalculation(int $pick, $id = NULL, string $type = 'update')
    {
        if ($id != NULL) {
            $pickTable = PickCalculation::where('machine_status_id', $id)->first();
            if(!$pickTable) {
                throw new Exception("Error Processing Request", 1);
            }

            $difference_pick = 0;
            $new_pick = 0;
            $total_pick = 0;

            if($pickTable->total_pick <= $pick) {
                $difference_pick = 0;
                $new_pick = 0;
                $total_pick = $pick;
            }
            else {
                if ($pickTable->new_pick <= $pick) {
                    $difference_pick = $pick - $pickTable->new_pick;
                    $new_pick = $pick;
                    $total_pick = $pickTable->total_pick + $difference_pick;
                }
                else {
                    $difference_pick = 0;
                    $new_pick = $pick;
                    $total_pick = $pickTable->total_pick + $pick;
                }
            }

            if ($type == "update") {
                $pickData = [
                    'shift_pick' => $total_pick - $pickTable->intime_pick,
                    'total_pick' => $total_pick,
                    'new_pick' => $new_pick,
                    'difference_pick' => $difference_pick,
                ];
                $updatePick = PickCalculation::where('id', $pickTable->id)->update($pickData);
                if($updatePick) {
                    return ['status' => true, 'isUpdate' => false, 'id' => 0];
                } else {
                    return ['status' => false, 'isUpdate' => false, 'id' => 0];
                }
            } 
            else {
                $pickData = [
                    'intime_pick' => $pickTable->total_pick,
                    'shift_pick' => $total_pick - $pickTable->total_pick,
                    'total_pick' => $total_pick,
                    'new_pick' => $new_pick,
                    'difference_pick' => $difference_pick,
                ];
                $insertPick = PickCalculation::create($pickData);
                if($insertPick) {
                    return ['status' => true, 'isUpdate' => true, 'id' => $insertPick->id];
                } else {
                    return ['status' => false, 'isUpdate' => true, 'id' => 0];
                }
            }
        }
        else {
            $pickData = [
                'intime_pick' => $pick,
                'shift_pick' => 0,
                'total_pick' => $pick,
                'new_pick' => 0,
                'difference_pick' => 0,
            ];
            $insertPick = PickCalculation::create($pickData);
            if($insertPick) {
                return ['status' => true, 'isUpdate' => true, 'id' => $insertPick->id];
            } else {
                return ['status' => false, 'isUpdate' => true, 'id' => 0];
            }
        }
    }
}
