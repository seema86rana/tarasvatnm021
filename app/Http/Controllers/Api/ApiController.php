<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Mail\ReportMail;
use App\Models\NodeMaster;
use App\Jobs\ProcessPacket;
use App\Models\MachineLog;
use App\Jobs\GenerateReport;
use App\Jobs\SendReportMail;
use Illuminate\Http\Request;
use App\Models\MachineMaster;
use App\Models\MachineStatus;
use App\Models\PickCalculation;
use App\Models\TempMachineStatus;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Barryvdh\DomPDF\Facade\Pdf;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
    }

    public function packet(Request $request)
    {
        try {
            $reqData = $request->all();
            // $this->logRequest($reqData);

            if (!is_array($reqData)) {
                return response()->json(['status' => false, 'message' => "Requested data is not in the correct format!"], 406);
            }

            if (empty($reqData['Ddt']) || empty($reqData['Did']) || empty($reqData['Tnd'])) {
                return response()->json(['status' => false, 'message' => "Required fields not passed (Ddt, Did, Tnd)!"], 400);
            }

            $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
            if (!$device) {
                return response()->json(["status" => false, "message" => "Device not found or not active!"], 404);
            }

            // ProcessPacket::dispatch($reqData)->delay(now()->addMinutes(2));
            ProcessPacket::dispatch($reqData);

            return response()->json(['status' => true, 'message' => "Success!"], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendReport()
    {
        $reportType = env('REPORT_TYPE', 'weekly');
        $reportFormat = env('REPORT_FORMAT', 'table');

        GenerateReport::dispatch($reportType, $reportFormat);
        return response()->json(['status' => true, 'message' => ucfirst($reportType) . ' report sent to the user with details.'], 200);
    }

    public function generateReport($filter, $format, $userId)
    {
        SendReportMail::dispatch($filter, $format, $userId);
        return response()->json(['status' => true, 'message' => 'Generate report sent on user Email and Whatsapp.'], 200);
    }

    public function runCommand()
    {
        try {
            Artisan::call('view:cache');
            Artisan::call('view:clear');
            Artisan::call('route:cache');
            Artisan::call('route:clear');
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            Artisan::call('optimize');
            Artisan::call('optimize:clear');

            return response()->json(['status' => true, 'message' => 'Artisan command executed.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** -----------------------------------------------------------------QUEUE function----------------------------------------------------------------- */
    /** ProcessPacket */
    public function packets(Request $request)
    {
        try {
            $reqData = $request->all();
            $path = public_path("assets/packet")."/".time()."-".date("d_F_Y-H_i_s_A").".json";
            file_put_contents($path, json_encode($reqData, JSON_PRETTY_PRINT));

            // echo "<pre>";
            // print_r($reqData);
            // die;

            if(!is_array($reqData)) {
                $returnData = [
                    "status" => false,
                    "message" => "Requested data is not a corrected format!",
                ];
                return response()->json($returnData, 406);
            }
            if (empty($reqData['Ddt']) || empty($reqData['Did']) || empty($reqData['Tnd'])) {
                return response()->json(['status' => false, 'message' => "Required fields not passed (Ddt, Did, Tnd)!"], 400);
            }
            $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
            if (!$device) {
                $returnData = [
                    "status" => false,
                    "message" => "Device not found or not active!",
                ];
                return response()->json($returnData, 404);
            }

            $this->processData($reqData);

            $returnData = [
                "status" => true,
                "message" => "Success!",
            ];
            return response()->json($returnData, 200);

        } catch (Exception $e) {
            $returnData = [
                "status" => false,
                "message" => $e->getMessage(),
            ];
            return response()->json($returnData, 500);
        }
    }

    protected function processData(array $reqData)
    {
        $totalNode  = $reqData['Tnd'];
        $staticTime = "07:59:59";

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
            $shiftDateType2 = $deviceDate;
            $shiftDateType1 = date('Y-m-d', strtotime('-1 day', strtotime($deviceDate)));
        }

        $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
        $shifts = json_decode($device->shift, true);

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
            Log::error("Error: Shift Start Date and Shift End Date are empty");
            throw new Exception("Shift Start Date and Shift End Date are empty");
        }

        $machineStatusId = MachineStatus::whereDate('shift_date', $shiftDate)
                            ->where('shift_start_datetime', $shiftStartDatetime)
                            ->where('shift_end_datetime', $shiftEndDatetime)
                            ->pluck('id');
        
        MachineStatus::whereIn('id', $machineStatusId)->update([
            'active_machine' => 0,
        ]);

        if (isset($reqData['Nd']) && is_array($reqData['Nd']) && count($reqData['Nd']) > 0) {

            foreach ($reqData['Nd'] as $node) {
                if (empty($node['Nid'])) {
                    continue;
                }
                $nodeName = 'N' . $node['Nid'];
                $nodeMasterTable = NodeMaster::where('device_id', $device->id)->where('name', $nodeName)->first();
                if (!$nodeMasterTable) {
                    $nodeMasterData = [
                        'device_id' => $device->id,
                        'name' => $nodeName,
                    ];
                    $nodeMasterTable = NodeMaster::create($nodeMasterData);
                }
        
                if (isset($node['Md']) && is_array($node['Md']) && count($node['Md']) > 0) {

                    foreach ($node['Md'] as $machine) {
                        if (empty($machine['Mid']) || empty($machine['Mdt'])) {
                            continue;
                        }
                        $utcMachineDatetime = Carbon::createFromFormat('Ymd H:i:s', $machine['Mdt'], env('DEVICE_TIMEZONE', 'UTC'));
                        $machineDatetime = $utcMachineDatetime->setTimezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');
                        $machineDate = date('Y-m-d', strtotime($machineDatetime));

                        $machineName = "{$nodeName}:M{$machine['Mid']}";
                        $machineMasterTable = MachineMaster::where('node_id', $nodeMasterTable->id)->where('name', $machineName)->first();
                        if (!$machineMasterTable) {
                            $machineMasterData = [
                                'node_id' => $nodeMasterTable->id,
                                'name' => $machineName,
                            ];
                            $machineMasterTable = MachineMaster::create($machineMasterData);
                        }

                        $machineLogData = [
                            'machine_id' => $machineMasterTable->id,
                            'speed' => $machine['Spd'] ?? '',
                            'mode' => $machine['St'] ?? 0,
                            'pick' => $machine['Tp'] ?? '',
                            'machine_datetime' => $machineDatetime,
                        ];
                        $machineLogTable = MachineLog::create($machineLogData);

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
                            'active_machine' => 1,
                            'speed' => (int)$machine['Spd'],
                            'status' => (int)$machine['St'] ?? 0,
                            'total_time' => $shiftStartTime->diffInMinutes($deviceTime),
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
                                $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                $diffMinTotalRunning += $lastRecTime->diffInMinutes($deviceTime);
                            }
                            else if ($machine['St'] == 1 && $machineStatusTable->status == 0) {
                                $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                                $diffMinLastStop += $lastRecTime->diffInMinutes($machineTime);
                                $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                $diffMinTotalRunning += $machineTime->diffInMinutes($deviceTime);
                            }
                            else if ($machine['St'] == 0 && $machineStatusTable->status == 1) {
                                $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage + 1;
                                $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                $diffMinLastRunning += $lastRecTime->diffInMinutes($machineTime);
                                $diffMinTotalRunning += $lastRecTime->diffInMinutes($machineTime);
                            }
                            else if ($machine['St'] == 0 && $machineStatusTable->status == 0) {
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
                                                    ->whereDate('shift_date', $shiftDate)
                                                    ->where('shift_start_datetime', $shiftStartDatetime)
                                                    ->where('shift_end_datetime', $shiftEndDatetime)
                                                    ->orderBy('id', 'desc')->first();

                            if ($machineStatusTableOld) {

                                $pickResponse = $this->pickCalculation((int)$machine['Tp'], $machineStatusTableOld->id, 'insert');

                                $diffMinLastStop = $machineStatusTableOld->last_stop ?? 0;
                                $diffMinLastRunning = $machineStatusTableOld->last_running ?? 0;

                                if ($machine['St'] == 1 && $machineStatusTableOld->status == 1) {
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    $diffMinLastStop = $diffMinLastStop;
                                    $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                }
                                else if ($machine['St'] == 1 && $machineStatusTableOld->status == 0) {
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    $diffMinLastStop += $shiftStartTime->diffInMinutes($machineTime);
                                    $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                }
                                else if ($machine['St'] == 0 && $machineStatusTableOld->status == 1) {
                                    $machineStatusData['no_of_stoppage'] = 1;
                                    $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinLastRunning += $shiftStartTime->diffInMinutes($machineTime);
                                }
                                else if ($machine['St'] == 0 && $machineStatusTableOld->status == 0) {
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinLastRunning = $diffMinLastRunning;
                                }
                                else {
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    $diffMinLastStop = 0;
                                    $diffMinLastRunning = 0;
                                }

                                if ($machine['St'] == 1) {
                                    $diffMinTotalRunning = $shiftStartTime->diffInMinutes($deviceTime);
                                }
                                else if ($machine['St'] == 0) {
                                    $diff = $shiftStartTime->diff($machineTime);
                                    $diffMinTotalRunning = $shiftStartTime > $machineTime ? 0 : $diff->h * 60 + $diff->i;
                                }
                                else {
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
                                    $diffMinLastStop = $shiftStartTime->diffInMinutes($machineTime);
                                    $diffMinLastRunning = $machineTime->diffInMinutes($deviceTime);
                                }
                                else if ($machine['St'] == 0) {
                                    $machineStatusData['no_of_stoppage'] = 1;
                                    $diffMinLastStop = $machineTime->diffInMinutes($deviceTime);
                                    $diffMinLastRunning = $shiftStartTime->diffInMinutes($machineTime);
                                }
                                else {
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    $diffMinLastStop = 0;
                                    $diffMinLastRunning = 0;
                                }

                                if ($machine['St'] == 1) {
                                    $diffMinTotalRunning = $shiftStartTime->diffInMinutes($deviceTime);
                                }
                                else if ($machine['St'] == 0) {
                                    $diff = $shiftStartTime->diff($machineTime);
                                    $diffMinTotalRunning = $shiftStartTime > $machineTime ? 0 : $diff->h * 60 + $diff->i;
                                }
                                else {
                                    $diffMinTotalRunning = 0;
                                }

                                if($diffMinLastStop > 0) {
                                    $machineStatusData['no_of_stoppage'] = 1;
                                }
                            }
                        }

                        $machineStatusData['efficiency']    = 0;
                        $machineStatusData['last_stop']     = $diffMinLastStop;
                        $machineStatusData['last_running']  = $diffMinLastRunning;
                        $machineStatusData['total_running'] = $diffMinTotalRunning;

                        if($machineStatusData['total_running'] != 0 && $machineStatusData['total_time'] != 0) {
                            $machineStatusData['efficiency'] = round((($machineStatusData['total_running'] / $machineStatusData['total_time']) * 100), 2);
                        }

                        if ($machineStatusTable) {
                            $updateMachineStatus = MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);

                            if($pickResponse['status']) {
                                if ($pickResponse['isUpdate'] && $pickResponse['id']) {
                                    $pickData = [
                                        'machine_status_id' => $machineStatusTable->id,
                                    ];
                                    PickCalculation::where('id', $pickResponse['id'])->update($pickData);
                                }
                            }
                            
                            //-----------------------------------------------------------------
                            $machineStatusData['machine_status_id'] = $machineStatusTable->id;
                            $machineStatusData['machine_log_id'] = $machineLogTable->id;
                            TempMachineStatus::create($machineStatusData);
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
                            TempMachineStatus::create($machineStatusData);
                            //-----------------------------------------------------------------
                        }
                    }
                }
            }
        }
        
        Log::info("Processing data Total Node -> {$totalNode} ::: " . json_encode($reqData));
        return true;
    }
    
    protected function pickCalculation(int $pick, int $id = NULL, string $type = 'update')
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

    /** SendReportMail */
    protected function sendReports()
    {
        $filter = env('REPORT_TYPE', 'weekly');
        $query = MachineStatus::with('machine.node.device.user');

        switch ($filter) {
            case 'daily':
                // $query->whereDate('machine_status.created_at', '2025-01-28');
                $query->whereDate('machine_status.created_at', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'yearly':
                $query->whereYear('machine_status.created_at', Carbon::now()->year);
                break;
            default:
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
        }

        // Fetch unique user IDs through relationship
        $userIds = $query->get()->pluck('machine.node.device.user.id')->unique();
        $users = User::whereIn('id', $userIds)->get();

        echo "<pre>";
        print_r($users->toArray());
        die;

        if ($users->isEmpty()) {
            Log::info("No data found for report type: {$filter}");
            return;
        }
    }

    /** GenerateReport */
    public function generateReports($filter, $format, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        $emailSubjectLabel = '';
        $previousDay = '';
        $currentDay = '';

        $userDetail = User::findOrFail($userId);
        
        $queryPrevious = MachineStatus::query()
            ->selectRaw("
                users.id AS user_id, devices.name AS device_name, node_master.name AS node_name, machine_master.name AS machine_name, machine_status.shift_name AS shift_name,
                DATE_FORMAT(MIN(machine_status.shift_start_datetime), '%h:%i %p') AS shift_start,
                DATE_FORMAT(MAX(machine_status.shift_end_datetime), '%h:%i %p') AS shift_end,
                SUM(machine_status.speed) AS speed,
                SUM(machine_status.efficiency) AS efficiency,
                SUM(machine_status.no_of_stoppage) AS stoppage,
                SUM(pick_calculations.shift_pick) AS pick,
                COUNT(users.id) AS total_record
            ")
            ->join('machine_master', 'machine_status.machine_id', '=', 'machine_master.id')
            ->join('node_master', 'machine_master.node_id', '=', 'node_master.id')
            ->join('devices', 'node_master.device_id', '=', 'devices.id')
            ->join('users', 'devices.user_id', '=', 'users.id')
            ->join('pick_calculations', 'machine_status.id', '=', 'pick_calculations.machine_status_id')
            ->where('users.id', $userId)
            ->groupBy('users.id', 'devices.name', 'node_master.name', 'machine_master.name', 'machine_status.machine_id', 'machine_status.shift_name');

        $queryCurrent = clone $queryPrevious;

        switch ($filter) {
            case 'daily':
                // $queryPrevious->whereDate('machine_status.created_at', '2025-01-27');
                // $queryCurrent->whereDate('machine_status.created_at', '2025-01-28');
                $queryPrevious->whereDate('machine_status.created_at', Carbon::yesterday());
                $queryCurrent->whereDate('machine_status.created_at', Carbon::today());
    
                $previousLabel = "Yesterday " . Carbon::yesterday()->format('d M Y');
                $currentLabel = "Today " . Carbon::today()->format('d M Y');
                $emailSubjectLabel = "Daily Comparison Report - [" . Carbon::yesterday()->format('d M Y') . " to " . Carbon::today()->format('d M Y') . "]";
                $previousDay = Carbon::yesterday()->format('d M Y');
                $currentDay = Carbon::today()->format('d M Y');
                break;
    
            case 'weekly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y') . "]";
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                break;
    
            case 'monthly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
    
                $previousLabel = "Last Month " . Carbon::now()->subMonth()->format('M Y');
                $currentLabel = "Current Month " . Carbon::now()->format('M Y');
                $emailSubjectLabel = "Monthly Comparison Report - [" . Carbon::now()->subMonth()->format('M Y') . " to " . Carbon::now()->format('M Y') . "]";
                $previousDay = Carbon::now()->subMonth()->format('M Y');
                $currentDay = Carbon::now()->format('M Y');
                break;
    
            case 'yearly':
                $queryPrevious->whereYear('machine_status.created_at', Carbon::now()->subYear()->year);
                $queryCurrent->whereYear('machine_status.created_at', Carbon::now()->year);
    
                $previousLabel = "Last Year " . Carbon::now()->subYear()->year;
                $currentLabel = "Current Year " . Carbon::now()->year;
                $emailSubjectLabel = "Yearly Comparison Report - [" . Carbon::now()->subYear()->year . " to " .  Carbon::now()->year . "]";
                $previousDay = Carbon::now()->subYear()->year;
                $currentDay = Carbon::now()->year;
                break;
    
            default:
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " .  Carbon::now()->endOfWeek()->format('d M Y') . "]";
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                break;
        }

        $previous = $queryPrevious->get();
        $current = $queryCurrent->get();

        $firstRec = $queryPrevious->first();
        if (!$firstRec) {
            $firstRec = $queryCurrent->first();
        }

        $totalLoop = max(count($previous), count($current));
        if ($totalLoop <= 0) {
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.'], 404);
        }

        // echo "<pre>";
        // print_r($firstRec->toArray());
        // echo "</pre>";
        // die;

        $reportData = [];
        if($format == env('REPORT_FORMAT', 'table'))
            for ($i = 0; $i < $totalLoop; $i++) {

                $nodeName = ($previous[$i]->node_name ?? $current[$i]->node_name);
                $shiftName = str_replace(" ", '', ($previous[$i]->shift_name ?? $current[$i]->shift_name));
                $preMachineName = ($previous[$i]->machine_name ?? $current[$i]->machine_name) . ' (Last)';
                $curMachineName = ($previous[$i]->machine_name ?? $current[$i]->machine_name) . ' (Current)';

                $reportData[$nodeName]['total_record'][$shiftName] = ($previous[$i]->total_record ?? $current[$i]->total_record);
                $reportData[$nodeName][$shiftName] = ($previous[$i]->shift_start ?? $current[$i]->shift_start) . ' - ' . ($previous[$i]->shift_end ?? $current[$i]->shift_end);
                
                $reportData[$nodeName]['efficiency'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'efficiency'), 2));
                $reportData[$nodeName]['efficiency'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'efficiency'), 2));

                $reportData[$nodeName]['speed'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'speed'), 2));
                $reportData[$nodeName]['speed'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'speed'), 2));
                
                $reportData[$nodeName]['pick'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'pick'), 2));
                $reportData[$nodeName]['pick'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'pick'), 2));
                
                $reportData[$nodeName]['stoppage'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'stoppage'), 2));
                $reportData[$nodeName]['stoppage'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'stoppage'), 2));
            }
        else {
            for ($i = 0; $i < $totalLoop; $i++) {
            
                $node = $previous[$i]->node_name ?? $current[$i]->node_name;
                $machineDisplayName = $previous[$i]->machine_name ?? $current[$i]->machine_name;
                $shiftName = str_replace(" ", '', ($previous[$i]->shift_name ?? $current[$i]->shift_name));
            
                // Build metrics with previous and current values
                $speed = [
                    'label' => $machineDisplayName,
                    'previous' => round((float)$this->getValue($previous, $i, 'speed'), 2),
                    'current' => round((float)$this->getValue($current, $i, 'speed'), 2),
                ];
                $efficiency = [
                    'label' => $machineDisplayName,
                    'previous' => round((float)$this->getValue($previous, $i, 'efficiency'), 2),
                    'current' => round((float)$this->getValue($current, $i, 'efficiency'), 2),
                ];
                $no_of_stoppage = [
                    'label' => $machineDisplayName,
                    'previous' => round((float)$this->getValue($previous, $i, 'no_of_stoppage'), 2),
                    'current' => round((float)$this->getValue($current, $i, 'no_of_stoppage'), 2),
                ];
                $shift_pick = [
                    'label' => $machineDisplayName,
                    'previous' => round((float)$this->getValue($previous, $i, 'shift_pick'), 2),
                    'current' => round((float)$this->getValue($current, $i, 'shift_pick'), 2),
                ];
            
                // Organize the result array
                $reportData[$node][$shiftName]['label'] = $node . ' (' . ucwords($filter) . ')' . ' (' . ($previous[$i]->shift_start ?? $current[$i]->shift_start) . ' - ' . ($previous[$i]->shift_end ?? $current[$i]->shift_end) . ')';
                $reportData[$node][$shiftName]['speed'][] = $speed;
                $reportData[$node][$shiftName]['efficiency'][] = $efficiency;
                $reportData[$node][$shiftName]['no_of_stoppage'][] = $no_of_stoppage;
                $reportData[$node][$shiftName]['shift_pick'][] = $shift_pick;
            }
        }

        // echo "<pre>";
        // print_r($reportData);
        // echo "</pre>";
        // die; 

        // return view('report.table', compact('reportData', 'filter', 'firstRec'));
        // $htmlFile = view('report.test-table')->render();
        $htmlFile = view('report.table', compact('reportData', 'filter', 'firstRec'))->render();
        $pdfFileName = "reports/pdf/" . uniqid() . ".pdf";
        $pdf = Pdf::loadHTML($htmlFile)->setPaper('a4', 'landscape');

        return $pdf->stream('report.pdf');

        // $pdfPath = public_path($pdfFileName);
        // $pdf->save($pdfPath);
        // return $pdfPath;

        if ($format == env('REPORT_FORMAT', 'table')) {
            return view('report.table', compact('reportData', 'filter', 'firstRec'));
        } else {
            return view('report.chart', compact('reportData', 'previousLabel', 'currentLabel'));
        }
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
    }
    /** -----------------------------------------------------------------QUEUE function----------------------------------------------------------------- */
}
