<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\NodeErrorLogs;
use App\Models\MachineMaster;
use App\Models\MachineLogs;
use App\Models\MachineStatus;
use Carbon\Carbon;

class ProcessPacket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reqData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $reqData)
    {
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
            // Example of business logic processing directly in the job
            if ($this->reqData['Did'] && $this->reqData['Tnd']) {
                $this->processData($this->reqData);
                \Log::info("Packet processed successfully: " . json_encode($this->reqData));
            } else {
                \Log::warning('Invalid data in request: ' . json_encode($this->reqData));
            }
        } catch (Exception $e) {
            \Log::error('Packet processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processData(array $reqData)
    {
        // Example of processing device (Did), node (Tnd), etc.
        $deviceId = $reqData['Did'];
        $totalNode = $reqData['Tnd'];
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $device = Device::where('name', $deviceId)->where('status', 1)->first();
        $nodeErrorLogsArray = [];
        $machineLogsArray = [];

        for ($i = 0; $i < $totalNode; $i++) {
            if (isset($reqData['Nd'][$i])) {
                $nodeData = $reqData['Nd'][$i];
                $nodeName = 'N-' . ($nodeData['Nid'] ?? md5(time().rand(11111, 99999)));

                $nodeMasterTable = NodeMaster::where('device_id', $device->id)->where('name', $nodeName)->first();
                if ($nodeMasterTable) {
                    $nodeErrorLogsData = [
                        'user_id' => $device->user_id,
                        'device_id' => $device->id,
                        'node_id' => $nodeMasterTable->id,
                        'status' => 2,
                    ];
                    $nodeErrorLogsArray[] = $nodeErrorLogsData;
                }
                else {
                    $nodeMasterData = [
                        'name' => $nodeName,
                        'user_id' => $device->user_id,
                        'device_id' => $device->id,
                        'no_of_nodes' => $totalNode,
                        'status' => 1,
                    ];
                    $nodeMasterTable = NodeMaster::create($nodeMasterData);
                }

                if (isset($nodeData['Md']) && is_array($nodeData['Md']) && count($nodeData['Md']) > 0) {
                    $machineData = $nodeData['Md'];
                    foreach ($machineData as $mKey => $mValue) {
                        if (!empty($mValue['Mid']) && isset($mValue['St']) && !empty($mValue['Mdt'])) {
                            $machineName = $nodeName . '_M-' . $mValue['Mid'];
                            $machineDisplayName = $nodeName . ':M-' . $mValue['Mid'];
                            $machineDatetime = Carbon::createFromFormat('Ymd h:i:s', $mValue['Mdt'])->format('Y-m-d H:i:s');
                            $currentDatetime = date('Y-m-d H:i:s');

                            $machineMasterTable = MachineMaster::where('node_id', $nodeMasterTable->id)->where('machine_name', $machineName)->first();
                            if (!$machineMasterTable) {
                                $machineMasterData = [
                                    'user_id' => $device->user_id,
                                    'device_id' => $device->id,
                                    'node_id' => $nodeMasterTable->id,
                                    'machine_name' => $machineName,
                                    'machine_display_name' => $machineDisplayName,
                                ];
                                $machineMasterTable = MachineMaster::create($machineMasterData);
                            }

                            $machineLogsData = [
                                'user_id' => $machineMasterTable->user_id,
                                'device_id' => $device->id,
                                'node_id' => $nodeMasterTable->id,
                                'machine_id' => $machineMasterTable->id,
                                'machine_datetime' => $machineDatetime,
                                'current_datetime' => $currentDatetime,
                                'mode' => $mValue['St'] ?? 0,
                                'speed' => $mValue['Spd'] ?? '',
                                'pick' => $mValue['Tp'] ?? '',
                            ];
                            $machineLogsArray[] = $machineLogsData;

                            // machine date is different for currend date
                            $machineDatetime = date("Y-m-d H:i:s", strtotime((date("Y-m-d").' '.date('H:i:s', strtotime($machineDatetime)))));
                            $deviceShift = json_decode($device->shift, true);
                            $machineDate = date('Y-m-d', strtotime($machineDatetime));
                            $shiftName = 'Shift D';
                            $shiftStart = $machineDate . ' 00:00:00';
                            $shiftEnd = $machineDate . ' 23:59:59';
                            foreach ($deviceShift as $dsKey => $dsValue) {
                                $shiftStart = date("y-m-d H:i:s", strtotime(($machineDate . " " . $dsValue['shift_start'])));
                                $shiftEnd = date("y-m-d H:i:s", strtotime(($machineDate . " " . $dsValue['shift_end'])));
                                if (strtotime($machineDatetime) >= strtotime($shiftStart) && strtotime($machineDatetime) <= strtotime($shiftEnd)) {
                                    $shiftName = $dsValue['shift_name'];
                                    break;
                                }
                            }

                            $machineLogsTable = MachineLogs::where('machine_id', $machineMasterTable->id)
                                                            ->where('device_id', $device->id)
                                                            ->where('user_id', $device->user_id)
                                                            ->where('node_id', $nodeMasterTable->id)
                                                            ->whereDate('machine_datetime', $machineDate);

                            $machineLogsSpeed = $machineLogsTable->get()->pluck('speed');
                            $machineLogsPick = $machineLogsTable->get()->pluck('pick');

                            $pickShiftWise = $machineLogsTable->whereBetween('machine_datetime', [$shiftStart, $shiftEnd])->pluck('pick');
                            $totalPick = array_sum(($pickShiftWise ? $pickShiftWise->toArray() : []));
                            if (strtotime($machineDatetime) >= strtotime($shiftStart) && strtotime($machineDatetime) <= strtotime($shiftEnd)) {
                                $totalPick += (int)$dsValue['shift_name'];
                            }
                            $machineLogsMode = $machineLogsTable->where('mode', 0)->get()->pluck('mode');
                            $stoppage = array_sum($machineLogsMode ? $machineLogsMode->toArray() : []);
                            if ($mValue['St'] == 0) {
                                $stoppage += 1;
                            }
                            $machineLogsLastRec = $machineLogsTable->orderBy('id', 'DESC')->first();
                            $lastRecDatetime = date('Y-m-d H:i:s');
                            if($machineLogsLastRec) {
                                $lastRecDatetime = $machineLogsLastRec->current_datetime;
                            }

                            $currentTime = Carbon::parse($currentDatetime);
                            $lastRecTime = Carbon::parse($lastRecDatetime);
                            $shiftStartTime = Carbon::parse($shiftStart);
                            $machineTime = Carbon::parse($machineDatetime);

                            $machineStatusTable = MachineStatus::where('machine_id', $machineMasterTable->id)
                                                ->where('device_id', $device->id)
                                                ->where('user_id', $device->user_id)
                                                ->where('node_id', $nodeMasterTable->id)
                                                ->where('shift_start_datetime', $shiftStart)
                                                ->where('shift_end_datetime', $shiftEnd)
                                                ->first();

                            $diffMinTotalRunning = $shiftStartTime->diffInMinutes($currentTime);
                            $diffMinLastStop = 0;
                            $diffMinLastRunning = 0;

                            if ($machineStatusTable) {
                                $diffMinLastStop = $machineStatusTable->last_stop ?? 0;
                                $diffMinLastRunning = $machineStatusTable->last_running ?? 0;

                                if ($mValue['St'] == 1 && $machineStatusTable->status == 1) {
                                    $diffMinLastStop = $diffMinLastStop;
                                    $diffMinLastRunning += $lastRecTime->diffInMinutes($currentTime);
                                }
                                else if ($mValue['St'] == 1 && $machineStatusTable->status == 0) {
                                    $diffMinLastStop += $lastRecTime->diffInMinutes($machineTime);
                                    $diffMinLastRunning += $machineTime->diffInMinutes($currentTime);
                                }
                                else if ($mValue['St'] == 0 && $machineStatusTable->status == 1) {
                                    $diffMinLastStop = $machineTime->diffInMinutes($currentTime);
                                    $diffMinLastRunning += $lastRecTime->diffInMinutes($machineTime);
                                }
                                else if ($mValue['St'] == 0 && $machineStatusTable->status == 0) {
                                    $diffMinLastStop += $lastRecTime->diffInMinutes($currentTime);
                                    $diffMinLastRunning = $diffMinLastRunning;
                                }
                                else {
                                    $diffMinLastStop = 0;
                                    $diffMinLastRunning = 0;
                                }
                            } 
                            else {
                                if ($mValue['St'] == 1) {
                                    $diffMinLastStop = $shiftStartTime->diffInMinutes($machineTime);
                                    $diffMinLastRunning = $machineTime->diffInMinutes($currentTime);
                                }
                                else if ($mValue['St'] == 0) {
                                    $diffMinLastStop = $machineTime->diffInMinutes($currentTime);
                                    $diffMinLastRunning = $shiftStartTime->diffInMinutes($machineTime);
                                }
                                else {
                                    $diffMinLastStop = 0;
                                    $diffMinLastRunning = 0;
                                }
                            }

                            $effiency = round((($diffMinLastRunning / $diffMinTotalRunning) * 100), 2);

                            $machineStatusData = [
                                'user_id' => $device->user_id,
                                'device_id' => $device->id,
                                'node_id' => $nodeMasterTable->id,
                                'machine_id' => $machineMasterTable->id,
                                'speed' => (int)$mValue['Spd'],
                                'avg_speed' => round(((array_sum($machineLogsSpeed ? $machineLogsSpeed->toArray() : []) + (int)$mValue['Spd']) / (count($machineLogsSpeed ? $machineLogsSpeed->toArray() : []) + 1)), 2),
                                'total_pick' => (int)$mValue['Tp'],
                                'avg_total_pick' => round(((array_sum($machineLogsPick ? $machineLogsPick->toArray() : []) + (int)$mValue['Tp']) / (count($machineLogsPick ? $machineLogsPick->toArray() : []) + 1)), 2),
                                'total_pick_shift_wise' => $totalPick,
                                'avg_efficiency' => $effiency,
                                'no_of_stoppage' => $stoppage,
                                'last_stop' => $diffMinLastStop,
                                'last_running' => $diffMinLastRunning,
                                'total_running' => $diffMinTotalRunning,
                                'shift_name' => $shiftName,
                                'shift_start_datetime' => $shiftStart,
                                'shift_end_datetime' => $shiftEnd,
                                'machine_date' => $machineDate,
                                'status' => $mValue['St'] ?? 0,
                            ];

                            if ($machineStatusTable) {
                                $updateMachineStatus = MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);
                            } else {
                                $insertMachineStatus = MachineStatus::create($machineStatusData);
                            }
                        }
                    }
                }
            }
            else {
                $totalNodeMaster = NodeMaster::where('device_id', $device->id)->count();
                if ($totalNodeMaster == $totalNode) {
                    continue;
                }
                else {
                    $nodeName = 'N-' . md5(time().rand(11111, 99999));
                    
                    $nodeMasterData = [
                        'name' => $nodeName,
                        'user_id' => $device->user_id,
                        'device_id' => $device->id,
                        'no_of_nodes' => $totalNode,
                        'status' => 1,
                    ];
                    $nodeMasterTable = NodeMaster::create($nodeMasterData);

                    $nodeErrorLogsData = [
                        'user_id' => $device->user_id,
                        'device_id' => $device->id,
                        'node_id' => $nodeMasterTable->id,
                        'status' => 1,
                    ];
                    $nodeErrorLogsArray[] = $nodeErrorLogsData;
                }
            }
        }

        if(count($nodeErrorLogsArray) > 0) {
            $insertNodeErrorLogs = NodeErrorLogs::insert($nodeErrorLogsArray);
        }
        if(count($machineLogsArray) > 0) {
            $insertMachineLogs = MachineLogs::insert($machineLogsArray);
        }
        
        \Log::info("Processing data for Device ID: {$deviceId}, Node Data: " . json_encode($nodeData));
    }  
}
