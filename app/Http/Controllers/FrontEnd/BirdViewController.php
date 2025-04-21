<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong!'
                ], 200);
            }

            $device = $user->device;
            if (!$device) {
                return response()->json([
                    'status' => false,
                    'message' => 'Device not found!'
                ], 200);
            }

            // Get all machine IDs under the user's device
            $machineIds = $device->load('nodes.machines')->nodes
                            ->flatMap(fn($node) => $node->machines->pluck('id'))->filter()->unique()->values()->toArray();
            if (empty($machineIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No machines found!'
                ], 200);
            }

            // Get the latest MachineStatus for the given date or fallback to any latest
            $machineStatus = MachineStatus::whereIn('machine_id', $machineIds)
                            ->whereDate('shift_date', $date)->orderByDesc('id')
                            ->first() ?? MachineStatus::whereIn('machine_id', $machineIds)->orderByDesc('id')->first();
            if (!$machineStatus) {
                return response()->json([
                    'status' => false,
                    'message' => 'Machine status not found!'
                ], 200);
            }

            // Fetch all machine statuses for the same shift
            $machineData = MachineStatus::with(['machine', 'pickCal'])
                            ->whereIn('machine_id', $machineIds)->whereDate('shift_date', $machineStatus->shift_date)
                            ->where('shift_start_datetime', $machineStatus->shift_start_datetime)->where('shift_end_datetime', $machineStatus->shift_end_datetime)
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
                'shiftStartEnd' => date('h:i A', strtotime($shiftStart)) . " - " . date('h:i A', strtotime($shiftEnd)),
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
