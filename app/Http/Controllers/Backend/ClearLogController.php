<?php

namespace App\Http\Controllers\Backend;

use DataTables;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use Illuminate\Http\Request;
use App\Models\MachineLog;
use App\Models\MachineMaster;
use App\Models\MachineStatus;
use App\Models\PickCalculation;
use App\Models\TempMachineStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Validator;

class ClearLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Clear Report Data | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('clear-reports.index') => 'Clear Report Data',
			'javascript: void(0)' => 'Clear',
		];

        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();
        $device = Device::where('status', 1)->get();
        $nodeMaster = NodeMaster::where('status', 1)->get();
        $machineMaster = MachineMaster::where('status', 1)->get();

        return view('backend.clear-report.index', compact('title', 'breadcrumbs', 'user', 'device', 'nodeMaster', 'machineMaster'));
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
        try {
            $user_id = $request->user_id;
            $device_id = $request->device_id;
            $select_shift = $request->select_shift;
            $select_shift_day = $request->select_shift_day;
            $node_id = $request->node_id;
            $machine_id = $request->machine_id;
            $selected_date = $request->date;
            $type = $request->type;

            if ($type === 'clearMachineLog') {

                // Define validation rules
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required_without_all:device_id,node_id,machine_id',
                    'device_id' => 'required_without_all:user_id,node_id,machine_id',
                    'node_id' => 'required_without_all:user_id,device_id,machine_id',
                    'machine_id' => 'required_without_all:user_id,device_id,node_id',
                ]);
            
                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['statusCode' => 0, 'message' => "At least one of user, device, node, or machine is required."]);
                }
            
                // Fetch IDs dynamically
                $deviceIds = $device_id ? [$device_id] : ($user_id ? Device::where('user_id', $user_id)->pluck('id')->toArray() : []);
                $nodeIds = $node_id ? [$node_id] : (!empty($deviceIds) ? NodeMaster::whereIn('device_id', $deviceIds)->pluck('id')->toArray() : []);
                $machineIds = $machine_id ? [$machine_id] : (!empty($nodeIds) ? MachineMaster::whereIn('node_id', $nodeIds)->pluck('id')->toArray() : []);

                // Extract shift times
                $startTime = $endTime = '';
                if ($select_shift) {
                    [$startTime, $endTime] = array_map(fn($t) => date('H:i:s', strtotime(trim($t))), explode(' - ', $select_shift));
                }
            
                // Extract shift days
                $startDay = $endDay = '';
                if ($select_shift_day) {
                    [$startDay, $endDay] = array_map('trim', explode(' - ', $select_shift_day));
                }
            
                // Query to filter machine status
                $query = MachineStatus::whereIn('machine_id', $machineIds);

                if ($selected_date) {
                    $startDate = Carbon::createFromFormat('m/d/Y', $selected_date);
                    $endDate = $startDate->copy()->addDay(); // Next day for shifts crossing midnight

                    if ($startTime && $endTime) {
                        // If shift ends the next day, adjust the end date
                        $modifyEndDate = ($endDay == '2') ? $endDate : $startDate;

                        $start_date = Carbon::createFromFormat('m/d/Y H:i:s', "{$selected_date} {$startTime}")
                            ->format('Y-m-d H:i:s');

                        $end_date = Carbon::createFromFormat('m/d/Y H:i:s', "{$modifyEndDate->format('m/d/Y')} {$endTime}")
                            ->format('Y-m-d H:i:s');

                        $query->whereBetween('device_datetime', [$start_date, $end_date]);
                        
                    } else {
                        // Default to full day if no shift time provided
                        $query->whereBetween('device_datetime', [
                            $startDate->startOfDay()->format('Y-m-d H:i:s'),
                            $startDate->endOfDay()->format('Y-m-d H:i:s')
                        ]);
                    }
                } elseif ($startTime && $endTime) {
                    // Filter only by time when no date is selected
                    $query->whereRaw("TIME(device_datetime) BETWEEN ? AND ?", [$startTime, $endTime]);
                }

                $machineStatusIds = $query->pluck('id')->toArray();

                // Delete related records in proper order
                PickCalculation::whereIn('machine_status_id', $machineStatusIds)->delete();
                TempMachineStatus::whereIn('machine_id', $machineIds)->delete();
                MachineStatus::whereIn('machine_id', $machineIds)->delete();
                MachineLog::whereIn('machine_id', $machineIds)->delete();
            
                return response()->json(['statusCode' => 1, 'message' => "Report data clear successfully."]);
            } else {
                $device = Device::when(!empty($user_id), function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })->where('status', 1)->get();

                $deviceShift = !empty($device_id) 
                    ? optional(Device::where('id', $device_id)->where('status', 1)->value('shift'), function ($shift) {
                        return json_decode($shift, true);
                    }) : [];

                $nodeMaster = NodeMaster::when(!empty($device_id), function ($query) use ($device_id) {
                    return $query->where('device_id', $device_id);
                })->where('status', 1)->get();

                $machineMaster = MachineMaster::when(!empty($node_id), function ($query) use ($node_id) {
                    return $query->where('node_id', $node_id);
                })->where('status', 1)->get();

                $response = [
                    'statusCode' => 1,
                    'device' => $device->isNotEmpty() ? $device->toArray() : [],
                    'deviceShift' => !empty($deviceShift) ? $deviceShift : [],
                    'nodeMaster' => $nodeMaster->isNotEmpty() ? $nodeMaster->toArray() : [],
                    'machineMaster' => $machineMaster->isNotEmpty() ? $machineMaster->toArray() : [],
                ];
                return response()->json($response);
            }
        } catch (Exception $error) {
            $response = array(
				'statusCode' => 0,
				'message' => $error->getMessage(),
			);
			return response()->json($response);
        }
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
