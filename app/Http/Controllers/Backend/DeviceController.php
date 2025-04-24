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
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

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
        $user = User::select('id', 'name')
                ->where('status', 1)
                ->whereNotIn('id', function ($query) {
                    $query->select('user_id')
                        ->from('devices')
                        ->whereNotNull('user_id');
                })
                ->orderBy('created_at', 'DESC')->get();
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

            /** Shift Calculation Start */
            $shiftLength = count($request->shift_name);
            $shiftArray = [];
            $diffinSecs = 0;
            $date = '2024-06-29';
            $date1 = '2024-06-29';
            $date2 = '2024-06-30';

            for ($i = 0; $i < $shiftLength; $i++) {
                $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                $end = Carbon::parse("{$date1} {$request->shift_end_time[$i]}");
    
                if ($start > $end) {
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_date' => 1,
                        'shift_start_time' => $request->shift_start_time[$i], 
                        'shift_end_date' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
    
                    $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    $end = Carbon::parse("{$date2} {$request->shift_end_time[$i]}");
                    $diffinSecs += $end->diffInSeconds($start);
                    $date1 = $date2;
                }
                else {
                    $startFirst = Carbon::parse("{$date1} {$request->shift_start_time[0]}");
                    $startCur = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    if ($startCur < $startFirst) { 
                        $date1 = $date2;
                    }

                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_date' => ($date == $date1) ? 1 : 2,
                        'shift_start_time' => $request->shift_start_time[$i], 
                        'shift_end_date' => ($date == $date1) ? 1 : 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
    
                    $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    $end = Carbon::parse("{$date1} {$request->shift_end_time[$i]}");
                    $diffinSecs += $end->diffInSeconds($start);
                }
            }

            if ($diffinSecs > 86400) {
                $times = self::formatTime($diffinSecs);
                $response = array(
                    'statusCode' => 0,
                    'message' => "Shift duration exceeds the limit! Total Shift Time: {$times}",
                );
                return response()->json($response);
            }

            $first = reset($shiftArray);
            $last = end($shiftArray);
            $firstShiftStartDatetime = Carbon::parse("{$date} {$first['shift_start_time']}");
            $lastShiftEndDate = ($last['shift_end_date'] == 2) ? $date2 : $date;
            $lastShiftEndDatetime = Carbon::parse("{$lastShiftEndDate} {$last['shift_end_time']}");
            $diffinSecs = $firstShiftStartDatetime->diffInSeconds($lastShiftEndDatetime);

            if ($diffinSecs > 86400) {
                $times = self::formatTime($diffinSecs);
                $response = array(
                    'statusCode' => 0,
                    'message' => "Shift duration exceeds the limit! Total Shift Time: {$times}",
                );
                return response()->json($response);
            }
            /** Shift Calculation End */
    
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
        $device = Device::findOrFail($id);
        $assignedUserId = $device->user_id;

        $user = User::select('id', 'name')
            ->where('status', 1)
            ->where(function ($query) use ($assignedUserId) {
                $query->whereNotIn('id', function ($subQuery) {
                    $subQuery->select('user_id')
                            ->from('devices')
                            ->whereNotNull('user_id');
                })
                ->orWhere('id', $assignedUserId);
            })->orderBy('created_at', 'DESC')->get();

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

            /** Shift Calculation Start */
            $shiftLength = count($request->shift_name);
            $shiftArray = [];
            $diffinSecs = 0;
            $date = '2024-06-29';
            $date1 = '2024-06-29';
            $date2 = '2024-06-30';

            for ($i = 0; $i < $shiftLength; $i++) {
                $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                $end = Carbon::parse("{$date1} {$request->shift_end_time[$i]}");
    
                if ($start > $end) {
                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_date' => 1,
                        'shift_start_time' => $request->shift_start_time[$i], 
                        'shift_end_date' => 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
    
                    $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    $end = Carbon::parse("{$date2} {$request->shift_end_time[$i]}");
                    $diffinSecs += $end->diffInSeconds($start);
                    $date1 = $date2;
                }
                else {
                    $startFirst = Carbon::parse("{$date1} {$request->shift_start_time[0]}");
                    $startCur = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    if ($startCur < $startFirst) { 
                        $date1 = $date2;
                    }

                    $shiftArray[] = [
                        'shift_name' => $request->shift_name[$i],
                        'shift_start_date' => ($date == $date1) ? 1 : 2,
                        'shift_start_time' => $request->shift_start_time[$i], 
                        'shift_end_date' => ($date == $date1) ? 1 : 2,
                        'shift_end_time' => $request->shift_end_time[$i],
                    ];
    
                    $start = Carbon::parse("{$date1} {$request->shift_start_time[$i]}");
                    $end = Carbon::parse("{$date1} {$request->shift_end_time[$i]}");
                    $diffinSecs += $end->diffInSeconds($start);
                }
            }

            if ($diffinSecs > 86400) {
                $times = self::formatTime($diffinSecs);
                $response = array(
                    'statusCode' => 0,
                    'message' => "Shift duration exceeds the limit! Total Shift Time: {$times}",
                );
                return response()->json($response);
            }

            $first = reset($shiftArray);
            $last = end($shiftArray);
            $firstShiftStartDatetime = Carbon::parse("{$date} {$first['shift_start_time']}");
            $lastShiftEndDate = ($last['shift_end_date'] == 2) ? $date2 : $date;
            $lastShiftEndDatetime = Carbon::parse("{$lastShiftEndDate} {$last['shift_end_time']}");
            $diffinSecs = $firstShiftStartDatetime->diffInSeconds($lastShiftEndDatetime);

            if ($diffinSecs > 86400) {
                $times = self::formatTime($diffinSecs);
                $response = array(
                    'statusCode' => 0,
                    'message' => "Shift duration exceeds the limit! Total Shift Time: {$times}",
                );
                return response()->json($response);
            }
            /** Shift Calculation End */

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

    private static function formatTime($seconds) {
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $secs = $seconds % 60;
        $mins = $minutes % 60;
        return ($hours > 0 ? "{$hours}hr " : "") . ($mins > 0 ? "{$mins}min " : "") . ($secs > 0 ? "{$secs}sec" : "");
    }
}
