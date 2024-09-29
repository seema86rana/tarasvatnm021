<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\NodeErrorLogs;
use App\Models\MachineMaster;
use App\Models\MachineLogs;
use App\Models\MachineStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Jobs\ProcessPacket;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ApiController extends Controller
{
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

            if (empty($reqData['Did']) || empty($reqData['Tnd'])) {
                return response()->json(['status' => false, 'message' => "Required fields not passed (Did, Tnd)!"], 400);
            }

            $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
            if (!$device) {
                return response()->json(["status" => false, "message" => "Device not found or not active!"], 404);
            }

            // Dispatch the job to process the packet data asynchronously
            // ProcessPacket::dispatch($reqData)->delay(now()->addMinutes(2));
            ProcessPacket::dispatch($reqData);

            return response()->json(['status' => true, 'message' => "Success!"], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
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

    public function runCommand($type, $mig = '')
    {
        try {
            if($type == 13579) {
                Artisan::call('view:cache');
                Artisan::call('view:clear');
                Artisan::call('route:cache');
                Artisan::call('route:clear');
                Artisan::call('config:cache');
                Artisan::call('config:clear');
                Artisan::call('optimize');
                Artisan::call('optimize:clear');

                if($mig == 97531) {
                    Artisan::call('migrate:refresh');
                    Artisan::call('db:seed');
                    
                    return response()->json(['status' => true, 'message' => 'Artisan command executed (database).'], 200);
                } 
                else if($mig == 13579) {
                    // Run the Artisan command 'queue:work'
                    Artisan::call('queue:work', [
                        '--stop-when-empty' => true,
                    ]);
                    
                    return response()->json(['status' => true, 'message' => 'Artisan command executed (queue).'], 200);
                }
                
                return response()->json(['status' => true, 'message' => 'Artisan command executed.'], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Artisan command can\'t executed!'], 401);
            }
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
            if (empty($reqData['Did']) || empty($reqData['Tnd'])) {
                $returnData = [
                    "status" => false,
                    "message" => "Required field not passed (like Did, Tnd)!",
                ];
                return response()->json($returnData, 400);
            }
            $device = Device::where('name', $reqData['Did'])->where('status', 1)->first();
            if (!$device) {
                $returnData = [
                    "status" => false,
                    "message" => "Device not found or not active!",
                ];
                return response()->json($returnData, 404);
            }

            // -----------------------------------------------------------------------------------------------------------------------------------------
            $deviceId = $reqData['Did'];
            $totalNode = $reqData['Tnd'];
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
                                $machineDatetime = Carbon::createFromFormat('Ymd H:i:s', $mValue['Mdt'])->format('Y-m-d H:i:s');
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
                                $currentDate = date('Y-m-d', strtotime($currentDatetime));
                                $shiftName = 'Shift D';
                                $shiftStart = $machineDate . ' 00:00:00';
                                $shiftEnd = $machineDate . ' 23:59:59';
                                foreach ($deviceShift as $dsKey => $dsValue) {
                                    // $shiftStart = date("Y-m-d H:i:s", strtotime(($machineDate . " " . $dsValue['shift_start'])));
                                    $shiftStart = date("Y-m-d H:i:s", strtotime(($currentDate . " " . $dsValue['shift_start'])));
                                    // $shiftEnd = date("Y-m-d H:i:s", strtotime(($machineDate . " " . $dsValue['shift_end'])));
                                    $shiftEnd = date("Y-m-d H:i:s", strtotime(($currentDate . " " . $dsValue['shift_end'])));
                                    if (strtotime($machineDatetime) >= strtotime($shiftStart) && strtotime($machineDatetime) < strtotime($shiftEnd)) {
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
                                    'total_pick' => (array_sum($machineLogsPick ? $machineLogsPick->toArray() : []) + (int)$mValue['Tp']),
                                    'avg_total_pick' => round(((array_sum($machineLogsPick ? $machineLogsPick->toArray() : []) + (int)$mValue['Tp']) / (count($machineLogsPick ? $machineLogsPick->toArray() : []) + 1)), 2),
                                    'efficiency' => $effiency,
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
                                    $machineStatusData['total_pick_shift_wise'] = (int)$mValue['Tp'] + $machineStatusTable->total_pick_shift_wise;
                                    $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage;
                                    if($mValue['St'] == 0) {
                                        $machineStatusData['no_of_stoppage'] = $machineStatusTable->no_of_stoppage + 1;
                                    }
                                    $updateMachineStatus = MachineStatus::where('id', $machineStatusTable->id)->update($machineStatusData);
                                } else {
                                    $machineStatusData['total_pick_shift_wise'] = (int)$mValue['Tp'];
                                    $machineStatusData['no_of_stoppage'] = 0;
                                    if($mValue['St'] == 0) {
                                        $machineStatusData['no_of_stoppage'] = 1;
                                    }
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
            // -----------------------------------------------------------------------------------------------------------------------------------------

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
