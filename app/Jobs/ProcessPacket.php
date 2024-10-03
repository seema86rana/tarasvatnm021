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
        $currentDatetime = date('Y-m-d H:i:s');

        $utcDeviceDatetime = Carbon::createFromFormat('Ymd H:i:s', $reqData['Ddt'], env('DEVICE_TIMEZONE', 'UTC'));
        $deviceDatetime = $utcDeviceDatetime->setTimezone(config('app.timezone', 'Asia/Kolkata'));
        $deviceDatetime = $deviceDatetime->format('Y-m-d H:i:s');

        $device = Device::where('name', $deviceId)->where('status', 1)->first();
        $nodeErrorLogsArray = [];
        $machineLogsArray = [];

        for ($i = 0; $i < $totalNode; $i++) {
            if (isset($reqData['Nd'][$i])) {
                $nodeData = $reqData['Nd'][$i];
                $nodeName = 'N' . ($nodeData['Nid'] ?? md5(time().rand(11111, 99999)));

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
                            $machineName = $nodeName . '-M' . $mValue['Mid'];
                            $machineDisplayName = $nodeName . ':M' . $mValue['Mid'];

                            $utcMachineDatetime = Carbon::createFromFormat('Ymd H:i:s', $mValue['Mdt'], env('DEVICE_TIMEZONE', 'UTC'));
                            $machineDatetime = $utcMachineDatetime->setTimezone(config('app.timezone', 'Asia/Kolkata'));
                            $machineDatetime = $machineDatetime->format('Y-m-d H:i:s');

                            $machineMasterTable = MachineMaster::where('node_id', $nodeMasterTable->id)->where('machine_name', $machineName)->first();
                            if (!$machineMasterTable) {
                                $machineMasterData = [
                                    'user_id' => $device->user_id,
                                    'device_id' => $device->id,
                                    'node_id' => $nodeMasterTable->id,
                                    'machine_name' => $machineName,
                                    'machine_display_name' => $machineDisplayName,
                                    'device_datetime' => $deviceDatetime,
                                ];
                                $machineMasterTable = MachineMaster::create($machineMasterData);
                            }
                            else {
                                $machineMasterData = [
                                    'device_datetime' => $deviceDatetime,
                                ];
                                MachineMaster::where('id', $machineMasterTable->id)->update($machineMasterData);
                            }

                            $machineLogsData = [
                                'user_id' => $machineMasterTable->user_id,
                                'device_id' => $device->id,
                                'node_id' => $nodeMasterTable->id,
                                'machine_id' => $machineMasterTable->id,
                                'machine_datetime' => $machineDatetime,
                                'device_datetime' => $deviceDatetime,
                                'current_datetime' => $currentDatetime,
                                'mode' => $mValue['St'] ?? 0,
                                'speed' => $mValue['Spd'] ?? '',
                                'pick' => $mValue['Tp'] ?? '',
                            ];
                            $machineLogsArray[] = $machineLogsData;

                            $deviceShift = json_decode($device->shift, true);
                            $machineDate = date('Y-m-d', strtotime($machineDatetime));
                            $deviceDate = date('Y-m-d', strtotime($deviceDatetime));
                            $currentDate = date('Y-m-d', strtotime($currentDatetime));
                            $shiftName = '';
                            $shiftStart = '';
                            $shiftEnd = '';
                            foreach ($deviceShift as $dsKey => $dsValue) {
                                $shiftStart = date("Y-m-d H:i:s", strtotime(($deviceDate . " " . $dsValue['shift_start'])));
                                $shiftEnd = date("Y-m-d H:i:s", strtotime(($deviceDate . " " . $dsValue['shift_end'])));
                                if (strtotime($deviceDatetime) >= strtotime($shiftStart) && strtotime($deviceDatetime) < strtotime($shiftEnd)) {
                                    $shiftName = $dsValue['shift_name'];
                                    break;
                                }
                            }

                            if(empty($shiftName) || empty($shiftStart) || empty($shiftEnd)) {
                                continue;
                            }

                            $machineLogsTable = MachineLogs::where('machine_id', $machineMasterTable->id)
                                                            ->where('device_id', $device->id)
                                                            ->where('user_id', $device->user_id)
                                                            ->where('node_id', $nodeMasterTable->id)
                                                            ->whereDate('machine_datetime', $machineDate)
                                                            ->orderBy('id', 'DESC')->first();

                            $lastRecDatetime = $deviceDatetime;
                            if($machineLogsTable) {
                                $lastRecDatetime = $machineLogsTable->device_datetime;
                            }

                            $deviceTime = Carbon::parse($deviceDatetime);
                            $lastRecTime = Carbon::parse($lastRecDatetime);
                            $shiftStartTime = Carbon::parse($shiftStart);
                            $machineTime = Carbon::parse($machineDatetime);

                            if ($nodeMasterTable->id == 1 && $machineMasterTable->id == 1) {
                                $path = public_path("assets/packet/n1m1.txt");

                                if (!file_exists(dirname($path))) {
                                    mkdir(dirname($path), 0755, true);
                                }
                            
                                $content = "-------------------------------- $currentDatetime --------------------------------" . PHP_EOL;
                                $content .= "Status: " . $mValue['St'];
                                $content .= "Device datetime: $deviceDatetime, ";
                                $content .= "lastRec datetime: $lastRecDatetime, ";
                                $content .= "Machine datetime: $machineDatetime, "  . PHP_EOL . PHP_EOL;
                            
                                if (file_put_contents($path, $content, FILE_APPEND) === false) {
                                    Log::error("Failed to write to the file: $path");
                                }
                            }

                            $machineStatusTable = MachineStatus::where('machine_id', $machineMasterTable->id)
                                                ->where('device_id', $device->id)
                                                ->where('user_id', $device->user_id)
                                                ->where('node_id', $nodeMasterTable->id)
                                                ->whereDate('machine_date', $machineDate)
                                                ->where('shift_start_datetime', $shiftStart)
                                                ->where('shift_end_datetime', $shiftEnd)
                                                ->first();

                            $diffMinLastStop = 0;
                            $diffMinLastRunning = 0;
                            $diffMinTotalRunning = 0;

                            $machineStatusData = [
                                'user_id' => $device->user_id,
                                'device_id' => $device->id,
                                'node_id' => $nodeMasterTable->id,
                                'machine_id' => $machineMasterTable->id,
                                'speed' => (int)$mValue['Spd'],
                                'total_pick' => (int)$mValue['Tp'],
                                'total_time' => $shiftStartTime->diffInMinutes($deviceTime),
                                'shift_name' => $shiftName,
                                'shift_start_datetime' => $shiftStart,
                                'shift_end_datetime' => $shiftEnd,
                                'machine_date' => $machineDate,
                                'status' => $mValue['St'] ?? 0,
                            ];

                            if ($machineStatusTable) {
                                $diffMinLastStop = $machineStatusTable->last_stop ?? 0;
                                $diffMinLastRunning = $machineStatusTable->last_running ?? 0;
                                $diffMinTotalRunning = $machineStatusTable->total_running ?? 0;

                                if ($mValue['St'] == 1 && $machineStatusTable->status == 1) {
                                    $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                                    $diffMinLastStop = $diffMinLastStop;
                                    $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinTotalRunning += $lastRecTime->diffInMinutes($deviceTime);
                                }
                                else if ($mValue['St'] == 1 && $machineStatusTable->status == 0) {
                                    $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                                    $diffMinLastStop += $lastRecTime->diffInMinutes($machineTime);
                                    $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinTotalRunning += $machineTime->diffInMinutes($deviceTime);
                                }
                                else if ($mValue['St'] == 0 && $machineStatusTable->status == 1) {
                                    $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage + 1;
                                    $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinLastRunning += $lastRecTime->diffInMinutes($machineTime);
                                    $diffMinTotalRunning += $lastRecTime->diffInMinutes($machineTime);
                                }
                                else if ($mValue['St'] == 0 && $machineStatusTable->status == 0) {
                                    $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                                    $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
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
                                $machineStatusTableOld = MachineStatus::where('machine_id', $machineMasterTable->id)
                                                    ->where('device_id', $device->id)
                                                    ->where('user_id', $device->user_id)
                                                    ->where('node_id', $nodeMasterTable->id)
                                                    ->whereDate('machine_date', $machineDate)
                                                    ->orderBy('id', 'desc')->first();

                                if ($machineStatusTableOld) {
                                    $machineStatusData['intime_pick'] = $machineStatusTableOld->total_pick;
                                    $machineStatusData['shift_pick'] = (int)$mValue['Tp'] - (int)$machineStatusData['intime_pick'];
                                    $diffMinLastStop = $machineStatusTableOld->last_stop ?? 0;
                                    $diffMinLastRunning = $machineStatusTableOld->last_running ?? 0;

                                    if ($mValue['St'] == 1 && $machineStatusTableOld->status == 1) {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop = $diffMinLastStop;
                                        $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 1 && $machineStatusTableOld->status == 0) {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop += $shiftStartTime->diffInMinutes($machineTime);
                                        $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 0 && $machineStatusTableOld->status == 1) {
                                        $machineStatusData['no_of_stoppage'] = 1;
                                        $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                        $diffMinLastRunning += $shiftStartTime->diffInMinutes($machineTime);
                                    }
                                    else if ($mValue['St'] == 0 && $machineStatusTableOld->status == 0) {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                        $diffMinLastRunning = $diffMinLastRunning;
                                    }
                                    else {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop = 0;
                                        $diffMinLastRunning = 0;
                                    }

                                    if ($mValue['St'] == 1) {
                                        $diffMinTotalRunning = $shiftStartTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 0) {
                                        $diffMinTotalRunning = $shiftStartTime->diffInMinutes($machineTime);
                                    }
                                    else {
                                        $diffMinTotalRunning = 0;
                                    }
                                }
                                else {
                                    $machineStatusTablePrevious = MachineStatus::where('machine_id', $machineMasterTable->id)
                                                            ->where('device_id', $device->id)
                                                            ->where('user_id', $device->user_id)
                                                            ->where('node_id', $nodeMasterTable->id)
                                                            ->orderBy('id', 'desc')->first();

                                    if ($machineStatusTablePrevious) {
                                        $machineStatusData['intime_pick'] = $machineStatusTablePrevious->total_pick;
                                        $machineStatusData['shift_pick'] = (int)$mValue['Tp'] - (int)$machineStatusData['intime_pick'];
                                    } else {
                                        $machineStatusData['intime_pick'] = $machineStatusData['total_pick'];
                                        $machineStatusData['shift_pick'] = 0;
                                    } 

                                    if ($mValue['St'] == 1) {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop = $shiftStartTime->diffInMinutes($machineTime);
                                        $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 0) {
                                        $machineStatusData['no_of_stoppage'] = 1;
                                        $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                        $diffMinLastRunning = $shiftStartTime->diffInMinutes($machineTime);
                                    }
                                    else {
                                        $machineStatusData['no_of_stoppage'] = 0;
                                        $diffMinLastStop = 0;
                                        $diffMinLastRunning = 0;
                                    }

                                    if ($mValue['St'] == 1) {
                                        $diffMinTotalRunning = $machineTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 0) {
                                        $diffMinTotalRunning = $shiftStartTime->diffInMinutes($machineTime);
                                    }
                                    else {
                                        $diffMinTotalRunning = 0;
                                    }

                                    if($diffMinLastStop > 0) {
                                        $machineStatusData['no_of_stoppage'] = 1;
                                    }
                                }
                            }

                            $machineStatusData['last_stop'] = $diffMinLastStop;
                            $machineStatusData['last_running'] = $diffMinLastRunning;
                            $machineStatusData['total_running'] = $diffMinTotalRunning;

                            if($machineStatusData['total_running'] != 0 && $machineStatusData['total_time'] != 0) {
                                $machineStatusData['efficiency'] = round((($machineStatusData['total_running'] / $machineStatusData['total_time']) * 100), 2);
                            } else {
                                $machineStatusData['efficiency'] = 0;
                            }

                            if ($machineStatusTable) {
                                if($machineStatusTable->intime_pick <= 0) {
                                    $machineStatusData['intime_pick'] = $machineStatusData['total_pick'];
                                    $machineStatusData['shift_pick'] = $machineStatusData['total_pick'];
                                } else {
                                    $machineStatusData['shift_pick'] = (int)$machineStatusData['total_pick'] - (int)$machineStatusTable->intime_pick;
                                }
                                MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);
                            } else {
                                MachineStatus::create($machineStatusData);
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
                    $nodeName = 'N' . md5(time().rand(11111, 99999));
                    
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
