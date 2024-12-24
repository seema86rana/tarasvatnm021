<?php

namespace App\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\NodeErrorLogs;
use App\Models\MachineMaster;
use App\Models\MachineLogs;
use App\Models\MachineStatus;
use App\Models\TempMachineStatus;
use App\Models\PickCalculation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Jobs\ProcessPacket;
// use Illuminate\Support\Facades\File;
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

    }

    public function generateReport($filter, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        
        $queryPrevious = MachineStatus::
                selectRaw("node_master.name, machine_status.user_id, machine_master.machine_display_name, SUM(machine_status.speed) as speed, SUM(machine_status.efficiency) as efficiency, SUM(machine_status.no_of_stoppage) as no_of_stoppage, SUM(pick_calculations.shift_pick) as shift_pick")
                ->leftJoin('node_master', 'machine_status.node_id', '=', 'node_master.id')
                ->leftJoin('machine_master', 'machine_status.machine_id', '=', 'machine_master.id')
                ->leftJoin('pick_calculations', 'machine_status.id', '=', 'pick_calculations.machine_status_id')
                ->where('machine_status.user_id', $userId);

        $queryCurrent = clone $queryPrevious;

        switch ($filter) {
            case 'daily':
                // $queryPrevious->whereDate('machine_status.created_at', '2024-12-10');
                // $queryCurrent->whereDate('machine_status.created_at', '2024-12-11');
                $queryPrevious->whereDate('machine_status.created_at', Carbon::yesterday());
                $queryCurrent->whereDate('machine_status.created_at', Carbon::today());
    
                $previousLabel = "Yesterday " . Carbon::yesterday()->format('d M Y');
                $currentLabel = "Today " . Carbon::today()->format('d M Y');
                break;
    
            case 'weekly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                break;
    
            case 'monthly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
    
                $previousLabel = "Last Month " . Carbon::now()->subMonth()->format('M Y');
                $currentLabel = "Current Month " . Carbon::now()->format('M Y');
                break;
    
            case 'yearly':
                $queryPrevious->whereYear('machine_status.created_at', Carbon::now()->subYear()->year);
                $queryCurrent->whereYear('machine_status.created_at', Carbon::now()->year);
    
                $previousLabel = "Last Year " . Carbon::now()->subYear()->year;
                $currentLabel = "Current Year " . Carbon::now()->year;
                break;
    
            default:
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                break;
        }

        $previous = $queryPrevious->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.machine_display_name')->get();
        $current = $queryCurrent->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.machine_display_name')->get();

        $totalLoop = max(count($previous->toArray()), count($current->toArray()));

        $resultArray = [];
        for ($i = 0; $i < $totalLoop; $i++) {
            
            $user = $previous[$i]->user_id ?? $current[$i]->user_id;
            $node = $previous[$i]->name ?? $current[$i]->name;
            $machineDisplayName = $previous[$i]->machine_display_name ?? $current[$i]->machine_display_name;
        
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
            $resultArray[$user][$node]['label'] = $node . ' (' . ucwords($filter) . ')';
            $resultArray[$user][$node]['speed'][] = $speed;
            $resultArray[$user][$node]['efficiency'][] = $efficiency;
            $resultArray[$user][$node]['no_of_stoppage'][] = $no_of_stoppage;
            $resultArray[$user][$node]['shift_pick'][] = $shift_pick;
        }

        foreach ($resultArray as $key => $value) {
            return view('report.pdf', compact('value', 'previousLabel', 'currentLabel'));
            $pdfData = view('report.pdf', compact('value', 'previousLabel', 'currentLabel'))->render();
        }

        return response()->json(['message' => 'PDF generated successfully', 'path' => $paths]);
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
                                'total_time' => $shiftStartTime->diffInMinutes($deviceTime),
                                'shift_name' => $shiftName,
                                'shift_start_datetime' => $shiftStart,
                                'shift_end_datetime' => $shiftEnd,
                                'machine_date' => $machineDate,
                                'status' => $mValue['St'] ?? 0,
                            ];

                            $pickResponse = [];

                            if ($machineStatusTable) {
                                $pickResponse = $this->pickCalculation((int)$mValue['Tp'], $machineStatusTable->id, 'update');

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
                                    $pickResponse = $this->pickCalculation((int)$mValue['Tp'], $machineStatusTableOld->id, 'insert');

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
                                        $diff = $shiftStartTime->diff($machineTime);
                                        $diffMinTotalRunning = $shiftStartTime > $machineTime ? 0 : $diff->h * 60 + $diff->i;
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
                                        $pickResponse = $this->pickCalculation((int)$mValue['Tp'],$machineStatusTablePrevious->id, 'insert');
                                    } else {
                                        $pickResponse = $this->pickCalculation((int)$mValue['Tp'], NULL, 'insert');
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
                                        $diffMinTotalRunning = $shiftStartTime->diffInMinutes($deviceTime);
                                    }
                                    else if ($mValue['St'] == 0) {
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

                            $machineStatusData['last_stop'] = $diffMinLastStop;
                            $machineStatusData['last_running'] = $diffMinLastRunning;
                            $machineStatusData['total_running'] = $diffMinTotalRunning;

                            if($machineStatusData['total_running'] != 0 && $machineStatusData['total_time'] != 0) {
                                $machineStatusData['efficiency'] = round((($machineStatusData['total_running'] / $machineStatusData['total_time']) * 100), 2);
                            } else {
                                $machineStatusData['efficiency'] = 0;
                            }

                            if ($machineStatusTable) {
                                // echo "<pre>";
                                // print_r($pickResponse);
                                // die;
                                $updateMachineStatus = MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);

                                if($pickResponse['status']) {
                                    if ($pickResponse['isUpdate'] && $pickResponse['id']) {
                                        $pickData = [
                                            'machine_status_id' => $machineStatusTable->id,
                                            'status' => 1,
                                        ];
                                        PickCalculation::where('id', $pickResponse['id'])->update($pickData);
                                    }
                                } else {
                                    // die('Something went wrong (update)!');
                                }
                                
                                //-----------------------------------------------------------------
                                $machineStatusData['machine_status_id'] = $machineStatusTable->id;
                                $machineStatusData['machine_log'] = json_encode($machineLogsData);
                                TempMachineStatus::insert($machineStatusData);
                                //-----------------------------------------------------------------
                            } else {
                                // echo "<pre>";
                                // print_r($pickResponse);
                                // die;
                                $insertMachineStatus = MachineStatus::create($machineStatusData);

                                if($pickResponse['status']) {
                                    if ($pickResponse['isUpdate'] && $pickResponse['id']) {
                                        $pickData = [
                                            'machine_status_id' => $insertMachineStatus->id,
                                            'status' => 1,
                                        ];
                                        PickCalculation::where('id', $pickResponse['id'])->update($pickData);
                                    }
                                } else {
                                    die('Something went wrong (insert)!');
                                }

                                //-----------------------------------------------------------------
                                $machineStatusData['machine_status_id'] = $insertMachineStatus->id;
                                $machineStatusData['machine_log'] = json_encode($machineLogsData);
                                TempMachineStatus::insert($machineStatusData);
                                //-----------------------------------------------------------------
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
    
    protected function pickCalculation(int $pick, int $id = NULL, string $type = 'update')
    {
        if ($id != NULL) {
            $pickTable = PickCalculation::where('machine_status_id', $id)->first();
            if(!$pickTable) {
                throw new pickCalculationException("Error Processing Request", 1);
                die;
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
                    'status' => 0,
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
                'status' => 0,
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
