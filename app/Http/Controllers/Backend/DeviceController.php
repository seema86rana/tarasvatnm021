<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use DataTables;

class DeviceController extends Controller
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
    public function index(Request $request)
    {
        if($request->ajax()) {
            $data = Device::with('user', 'createdBy')->orderBy('created_at','DESC')->get();
            $i = 0;
            return Datatables::of($data)
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('user', function ($row) {
                    return !empty($row->user->name) ? $row->user->name : '--------';
                })
                ->addColumn('shift', function ($row) {
                    return '<button class="text-primary btn btn-outline-secondary show-shift" data-id="' . $row->id . '" title="Show Shift" style="text-align: center;display: block;">
                                <div style="display: none;" id="shift_' . $row->id . '">' . $row->shift . '</div>
                                <i class="icon-eye2"></i>
                            </button>';
                })
                ->addColumn('status', function ($row) {
                    return '<select class="form-control status-device" data-id="' . $row->id . '">
                                <option value="1" ' . ($row->status == 1 ? 'selected' : '') . '>Active</option>
                                <option value="2" ' . ($row->status == 2 ? 'selected' : '') . '>Inactive</option>
                            </select>';
                })                
                ->addColumn('created_at', function ($row) {
                    return date('d/m/Y', strtotime($row->created_at));
                })
                ->addColumn('created_by', function ($row) {
                    return !empty($row->createdBy->name) ? $row->createdBy->name : '--------';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="text-info btn btn-outline-secondary edit-device" data-id="' . $row->id . '" title="Edit">
                                <i class="icon-pencil7"></i>
                            </button>
                            &nbsp;&nbsp;
                            <button class="text-danger btn btn-outline-secondary delete-device" data-id="' . $row->id . '" title="Delete">
                                <i class="icon-trash"></i>
                            </button>';
                })
                ->rawColumns(['serial_no', 'user', 'shift', 'status', 'created_at', 'created_by', 'action'])
                ->make(true);
        }

        $title = "Device | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('devices.index') => 'Device',
			'javascript: void(0)' => 'List',
		];
        return view('backend.device.index', compact('title', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modal_title = "Add Device";
        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();
        return response()->json([
            'statusCode' => 1,
            'html' => View::make("backend.device.add_and_edit", compact('modal_title', 'user'))->render(),
        ]);
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
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'unique:devices,name'],
                'user_id' => ['required', 'numeric'],
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                $response = array(
                    'statusCode' => 0,
                    'message' => $error,
                );
                return response()->json($response);
            }

            $shiftLength = count($request->shift_name);
            $shiftArray = [];
            $shiftType = 0; // Tracks shifts spanning multiple days

            for ($i = 0; $i < $shiftLength; $i++) {
                $shiftStart = strtotime($request->shift_start_time[$i]);
                $shiftEnd = strtotime($request->shift_end_time[$i]);

                /*
                // Validate shift times
                if ($shiftStart > $shiftEnd && $i == 0) {
                    return response()->json([
                        'statusCode' => 0,
                        'message' => "Shift start time should not be greater than shift end time for Shift $i",
                        'position_start' => $i,
                        'position_end' => $i,
                    ]);
                } elseif ($i > 0 && strtotime($request->shift_end_time[$i - 1]) > $shiftStart) {
                    return response()->json([
                        'statusCode' => 0,
                        'message' => "Shift start time for Shift $i overlaps with the previous shift",
                        'position_start' => $i,
                        'position_end' => $i - 1,
                    ]);
                } elseif ($shiftType != 0) {
                    $shiftEndDateTime = date('Y-m-d H:i:s', strtotime("2025-01-26 {$request->shift_end_time[$i]}"));
                
                    // Get the first shift's start datetime
                    $firstShiftStartDateTime = date('Y-m-d H:i:s', strtotime("2025-01-25 {$request->shift_start_time[0]}"));
                
                    // Calculate the time difference in minutes
                    $timeDifferenceInMinutes = (strtotime($shiftEndDateTime) - strtotime($firstShiftStartDateTime)) / 60;
                
                    if ($timeDifferenceInMinutes > 1440) { // 24 hours = 1440 minutes
                        return response()->json([
                            'statusCode' => 0,
                            'message' => "Shift end time for Shift $i exceeds 24 hours from the first shift's start time",
                            'position_start' => 0,
                            'position_end' => $i, // Highlight the first shift and the current shift
                        ]);
                    }
                }
                */

                // Determine shift start and end days
                if ($shiftStart < strtotime($request->shift_start_time[0])) {
                    $shiftType++;
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 2,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                } elseif ($shiftStart > $shiftEnd) {
                    $shiftType++;
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 1,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                } else {
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 1,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 1,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                }
            }
    
            $data = $validator->validated();
            $data['shift'] = json_encode($shiftArray);
            $data['status'] = 1;
            $data['created_by'] = Auth::user()->id;
            $device = Device::create($data);
            if(!$device) {
                $response = array(
                    'statusCode' => 0,
                    'message' => "Something went wrong!",
                );
                return response()->json($response);
            }

            $response = array(
                'statusCode' => 1,
                'message' => "Device created successfully!",
            );
            return response()->json($response);
        } catch (\Exception $error) {
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
    public function show($id, Request $request)
    {
        try {
            // Find the device to update
            $device = Device::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'status' => ['required'],
            ]);

            // Check for validation failure
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 0,
                    'message' => $validator->errors()->first(),
                ]);
            }

            // Update the device with validated data
            $data = $validator->validated();
            $data['updated_by'] = Auth::user()->id;
            
            // Update device data
            $device->update($data);

            return response()->json([
                'statusCode' => 1,
                'message' => 'Device status updated successfully!',
            ]);
        } catch (\Exception $error) {
            $response = array(
				'statusCode' => 0,
				'message' => $error->getMessage(),
			);
			return response()->json($response);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modal_title = "Update Device";
        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();
        $device = Device::where('id', $id)->first();
        if (!$device) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Device not found!',
            ]);
        }

        return response()->json([
            'statusCode' => 1,
            'html' => View::make("backend.device.add_and_edit", compact('modal_title', 'device', 'user'))->render(),
        ]);
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
        try {
            // Find the device to update
            $device = Device::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'unique:devices,name,' . $device->id],
                'user_id' => ['required', 'numeric'],
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                $response = array(
                    'statusCode' => 0,
                    'message' => $error,
                );
                return response()->json($response);
            }

            $shiftLength = count($request->shift_name);
            $shiftArray = [];
            $shiftType = 0; // Tracks shifts spanning multiple days

            for ($i = 0; $i < $shiftLength; $i++) {
                $shiftStart = strtotime($request->shift_start_time[$i]);
                $shiftEnd = strtotime($request->shift_end_time[$i]);

                /*
                // Validate shift times
                if ($shiftStart > $shiftEnd && $i == 0) {
                    return response()->json([
                        'statusCode' => 0,
                        'message' => "Shift start time should not be greater than shift end time for Shift $i",
                        'position_start' => $i,
                        'position_end' => $i,
                    ]);
                } elseif ($i > 0 && strtotime($request->shift_end_time[$i - 1]) > $shiftStart) {
                    return response()->json([
                        'statusCode' => 0,
                        'message' => "Shift start time for Shift $i overlaps with the previous shift",
                        'position_start' => $i,
                        'position_end' => $i - 1,
                    ]);
                } elseif ($shiftType != 0) {
                    $shiftEndDateTime = date('Y-m-d H:i:s', strtotime("2025-01-26 {$request->shift_end_time[$i]}"));
                
                    // Get the first shift's start datetime
                    $firstShiftStartDateTime = date('Y-m-d H:i:s', strtotime("2025-01-25 {$request->shift_start_time[0]}"));
                
                    // Calculate the time difference in minutes
                    $timeDifferenceInMinutes = (strtotime($shiftEndDateTime) - strtotime($firstShiftStartDateTime)) / 60;
                
                    if ($timeDifferenceInMinutes > 1440) { // 24 hours = 1440 minutes
                        return response()->json([
                            'statusCode' => 0,
                            'message' => "Shift end time for Shift $i exceeds 24 hours from the first shift's start time",
                            'position_start' => 0,
                            'position_end' => $i, // Highlight the first shift and the current shift
                        ]);
                    }
                }
                */

                // Determine shift start and end days
                if ($shiftStart < strtotime($request->shift_start_time[0])) {
                    $shiftType++;
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 2,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                } elseif ($shiftStart > $shiftEnd) {
                    $shiftType++;
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 1,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                } else {
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_day' => 1,
                        'shift_start_time' => $request->shift_start_time[$i],
                        'shift_end_day' => 1,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
                }
            }

            $data = $validator->validated();
            $data['shift'] = json_encode($shiftArray);
            $data['updated_by'] = Auth::user()->id;

            // Update device data
            $device->update($data);
            return response()->json([
                'statusCode' => 1,
                'message' => 'Device updated successfully!',
            ]);
        } catch (\Exception $error) {
            $response = array(
				'statusCode' => 0,
				'message' => $error->getMessage(),
			);
			return response()->json($response);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Device not found!',
            ]);
        }

        // Attempt to delete the device
        if ($device->delete()) {
            return response()->json([
                'statusCode' => 1,
                'message' => 'Device deleted successfully!',
            ]);
        } else {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Error occurred while deleting the device.',
            ]);
        }
    }
}
