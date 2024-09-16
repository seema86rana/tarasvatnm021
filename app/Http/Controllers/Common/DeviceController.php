<?php

namespace App\Http\Controllers\Common;

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
                    return date('dS F Y', strtotime($row->created_at));
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
        return view('common.device.index', compact('title', 'breadcrumbs'));
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
            'html' => View::make("common.device.add_and_edit", compact('modal_title', 'user'))->render(),
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
                'name' => ['required', 'string'],
                'user_id' => ['required', 'numeric'],
                'device_id' => ['required', 'string', 'unique:devices,device_id'],
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
            for ($i=0; $i < $shiftLength; $i++) { 
                $shiftArray[] = [
                    'shift_name' => $request->shift_name[$i],
                    'shift_start' => $request->shift_start[$i],
                    'shift_end' => $request->shift_end[$i],
                ];
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
        } catch (Exception $error) {
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
            'html' => View::make("common.device.add_and_edit", compact('modal_title', 'device', 'user'))->render(),
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
                'name' => ['required', 'string'],
                'user_id' => ['required', 'numeric'],
                'device_id' => ['required', 'string', 'unique:devices,device_id,' . $device->id],
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
            for ($i=0; $i < $shiftLength; $i++) { 
                $shiftArray[] = [
                    'shift_name' => $request->shift_name[$i],
                    'shift_start' => $request->shift_start[$i],
                    'shift_end' => $request->shift_end[$i],
                ];
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
        } catch (Exception $error) {
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
