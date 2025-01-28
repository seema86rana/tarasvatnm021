<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\MachineStatus;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BirdViewController extends Controller
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
        if (Auth::user()->role_id == 0) {
            return redirect()->route('dashboard.index');
        }
        $title = "Bird View";
        return view('frontend.birdview.index', compact('title'));
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
        $datetime = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        try {
            $authId = Auth::user()->id;
            if (!$authId) {
                return response()->json(['status' => false, 'message' => 'Something went wrong!'], 200);
            }
            
            $deviceData = Device::with([])->where('user_id', $authId)->where('status', 1)->get();
            if (!$deviceData) {
                return response()->json(['status' => false, 'message' => 'Device not found!'], 200);
            }

            $machineId = [];

            foreach ($deviceData as $device) {
                if($device->nodes) {
                    foreach($device->nodes as $node) {
                        if($node->status == 1 && $node->machines) {
                            foreach($node->machines as $machine) {
                                if($machine->status == 1) {
                                    $machineId[] = $machine->id;
                                }
                            }
                        }
                    }
                    if(count($machineId) > 0) {
                        break;
                    }
                }
            }

            if(count($machineId) <= 0) {
                return response()->json(['status' => false, 'message' => 'Machine not found!'], 200);
            }

            $machineStatus = MachineStatus::whereIn('machine_id', $machineId)->whereDate('shift_date', $date)->orderBy('id', 'desc')->first();
            if (!$machineStatus) {
                $machineStatus = MachineStatus::whereIn('machine_id', $machineId)->orderBy('id', 'desc')->first();
            }
            if(!$machineStatus) {
                return response()->json(['status' => false, 'message' => 'Machine status not found'], 200);
            }

            $machineData = MachineStatus::with('machine', 'pickCal')
                            ->whereIn('machine_id', $machineId) 
                            ->whereDate('shift_date', $machineStatus->shift_date)
                            ->where('shift_start_datetime', $machineStatus->shift_start_datetime)
                            ->where('shift_end_datetime', $machineStatus->shift_end_datetime)
                            ->get();

            $shiftName = $machineStatus->shift_name;
            $shiftStart = $machineStatus->shift_start_datetime;
            $shiftEnd = $machineStatus->shift_end_datetime;

            // echo "<pre>";
            // print_r($machineData->toArray());
            // echo "</pre>";
            // die;

            return response()->json([
                'status' => true,
                'shiftName' => $shiftName,
                'shiftStart' => date('h:i A', strtotime($shiftStart)),
                'shiftEnd' => date('h:i A', strtotime($shiftEnd)),
                'shiftStartEnd' => date('h:i A', strtotime($shiftStart)) . " " . date('h:i A', strtotime($shiftEnd)),
                'html' => View::make('frontend.birdview.bird', compact('machineData'))->render(),
            ]);

        } catch(\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
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
