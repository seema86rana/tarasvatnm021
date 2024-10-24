<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\MachineMaster;
use App\Models\MachineStatus;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
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
                return response()->json(['status' => false, 'message' => 'Something went wrong!'], 201);
            }
            
            $deviceData = Device::where('user_id', $authId)->where('status', 1)->get();
            if (!$deviceData) {
                return response()->json(['status' => false, 'message' => 'Device not found!'], 201);
            }
            $shiftName = 'Shift D';
            $shiftStart = '00:00 AM';
            $shiftEnd = '00:00 PM';
            $getShiftStart = $datetime;
            $getShiftEnd = $datetime;
            $getShiftStartArray = [];
            $getShiftEndArray = [];
            $dId = [];
            foreach ($deviceData as $dKey => $dValue) {
                $deviceShift = json_decode($dValue->shift, true);
                foreach ($deviceShift as $dsKey => $dsValue) {
                    $getShiftStart = date("Y-m-d H:i:s", strtotime(($date . " " . $dsValue['shift_start'])));
                    $getShiftEnd = date("Y-m-d H:i:s", strtotime(($date . " " . $dsValue['shift_end'])));
                    if (strtotime($datetime) >= strtotime($getShiftStart) && strtotime($datetime) < strtotime($getShiftEnd)) {
                        $shiftName = $dsValue['shift_name'];
                        $shiftStart = date('h:i A', strtotime($getShiftStart));
                        $shiftEnd = date('h:i A', strtotime($getShiftEnd));
                        $dId[] = $dValue->id;
                        $getShiftStartArray[] = $getShiftStart;
                        $getShiftEndArray[] = $getShiftEnd;
                        break;
                    }
                }
            }
            
            $machineData = MachineStatus::with('machineMaster', 'pickCalculation')
                            ->whereIn('device_id', $dId)
                            ->whereDate('machine_date', date('Y-m-d'))
                            ->whereIn('shift_start_datetime', $getShiftStartArray)
                            ->whereIn('shift_end_datetime', $getShiftEndArray)
                            ->where('user_id', $authId)->get();

            if(count($machineData->toArray()) <= 0) {
                $machineSingleData = MachineStatus::with('machineMaster', 'pickCalculation')
                                ->where('user_id', $authId)->orderBy('id', 'DESC')->first();

                if($machineSingleData) {
                    $machineData = MachineStatus::with('machineMaster', 'pickCalculation')
                                ->where('device_id', $machineSingleData->device_id)
                                ->where('machine_date', $machineSingleData->machine_date)
                                ->where('shift_name', $machineSingleData->shift_name)
                                ->where('shift_start_datetime', $machineSingleData->shift_start_datetime)
                                ->where('shift_end_datetime', $machineSingleData->shift_end_datetime)
                                ->where('user_id', $authId)->get();
                }
            }

            // echo "<pre>";
            // print_r($machineData->toArray());
            // die;

            return response()->json([
                'status' => true,
                'shiftName' => $shiftName,
                'shiftStart' => $shiftStart,
                'shiftEnd' => $shiftEnd,
                'shiftStartEnd' => $shiftStart . ' - ' . $shiftEnd,
                'html' => View::make('frontend.birdview.bird', compact('machineData'))->render(),
            ]);

        } catch(Exception $e) {
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
