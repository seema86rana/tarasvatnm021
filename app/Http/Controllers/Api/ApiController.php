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
        GenerateReport::dispatch($reportType);
        return response()->json(['status' => true, 'message' => ucfirst($reportType) . ' report sent to the user with details.'], 200);
    }

    public function generateReport($filter, $userId)
    {
        SendReportMail::dispatch($filter, $userId);
        return response()->json(['status' => true, 'message' => 'Generate report sent on user Email and Whatsapp.'], 200);
    }

    /*
    private function logRequest($reqData)
    {
        $path = public_path("assets/packet") . "/" . time() . "-" . date("d_F_Y-H_i_s_A") . ".json";
        file_put_contents($path, json_encode($reqData, JSON_PRETTY_PRINT));
    }

    public function packetTest(Request $request) 
    {
        $folderPath = public_path("assets/packet");
        $jsonFiles = File::files($folderPath);
        $returnData = [];
        foreach ($jsonFiles as $file) {
            if ($file->getExtension() === 'json') {
                $content = File::get($file->getRealPath());
                $jsonData = json_decode($content, true);
                // echo "<pre>";
                // print_r($content);
                // die;
                $returnData[] = $this->callAPI($content);
                echo "<pre>";
                print_r($returnData);
                die;
            }
        }
        return response()->json($returnData, 200);
    }

    private function callAPI($data) 
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://127.0.0.1:7008/api/packet',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => "'".$data."'",
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    */

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

            /*
            if($mig == 97531) {
                Artisan::call('migrate:refresh');
                Artisan::call('db:seed');
                
                return response()->json(['status' => true, 'message' => 'Artisan command executed (database).'], 200);
            } 
            else if($mig == 13579) {
                Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                ]);
                
                return response()->json(['status' => true, 'message' => 'Artisan command executed (queue).'], 200);
            }
            */
            return response()->json(['status' => true, 'message' => 'Artisan command executed.'], 200);
            
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** -----------------------------------------------------------------QUEUE function----------------------------------------------------------------- */
    /** ProcessPacket */
    public function index(Request $request)
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
        $query = MachineStatus::with('user');

        switch ($filter) {
            case 'daily':
                $query->whereDate('machine_status.created_at', '2024-12-11');
                // $query->whereDate('machine_status.created_at', Carbon::today());
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

        $result = $query->distinct()->pluck('user_id');
        $users = User::whereIn('id', $result)->get();

        // echo "<pre>";
        // print_r($users->toArray());
        // die;

        if ($users->isEmpty()) {
            Log::info("No data found for report type: {$filter}");
            return;
        }

        foreach ($users as $key => $user) {
            // $fileName = $this->generateReport($filter, $user->id, 'direct');
            $fileName = "";
            $this->sendOnEmail($user, $filter, $fileName);
            $this->sendOnWhatsApp($user, $filter, $fileName);
        }
    }

    private function sendOnEmail(object $user, string $reportType, string $fileName)
    {
        $mailData = [
            'companyName' => ucwords(str_replace("_", " ", config('app.name', 'TARASVAT Industrial Electronics'))),
            'reportType' => ucfirst($reportType),
            'reportDate' => now()->toDateString(),
            'reportLink' => asset('/') . "api/generate-report/$reportType/$user->id",
            // 'reportLink' => route('generate.report', [$reportType, $user->id]),
            // 'reportLink' => asset('/') . "reports/html/$fileName",
        ];

        Mail::to($user->email)->send(new ReportMail($mailData, $fileName));
    }

    private function sendOnWhatsApp(object $user, string $reportType, string $fileName)
    {
        // Add WhatsApp sending logic here
    }
    /** -----------------------------------------------------------------QUEUE function----------------------------------------------------------------- */
}
