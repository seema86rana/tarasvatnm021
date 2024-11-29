<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\MachineMaster;
use App\Models\MachineLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use DataTables;

class ReportController extends Controller
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

            $user_id = $request->user_id;
            $device_id = $request->device_id;
            $node_id = $request->node_id;
            $machine_id = $request->machine_id;
            $date = $request->date;
            $start = $request->start;
            $length = $request->length;

            $query = MachineLogs::with('user', 'device', 'node', 'machine')
                ->when(!empty($user_id), function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->when(!empty($device_id), function ($query) use ($device_id) {
                    return $query->where('device_id', $device_id);
                })
                ->when(!empty($node_id), function ($query) use ($node_id) {
                    return $query->where('node_id', $node_id);
                })
                ->when(!empty($machine_id), function ($query) use ($machine_id) {
                    return $query->where('machine_id', $machine_id);
                })
                ->when(!empty($date), function ($query) use ($date) {
                    return $query->whereDate('device_datetime', date('Y-m-d', strtotime($date)));
                });
                
                $totalRecord = $query->count();
                $data = $query->orderBy('id','ASC');

            $i = $start;
            return Datatables::of($data)
                ->setTotalRecords($totalRecord) // Important for pagination with large data
                ->setFilteredRecords($totalRecord) // If you implement search, update this dynamically
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('user', function ($row) {
                    return !empty($row->user->name) ? $row->user->name : '--------';
                })
                ->addColumn('device', function ($row) {
                    return !empty($row->device->name) ? $row->device->name : '--------';
                })
                ->addColumn('node', function ($row) {
                    return !empty($row->node->name) ? $row->node->name : '--------';
                })
                ->addColumn('machine', function ($row) {
                    return !empty($row->machine->machine_display_name) ? $row->machine->machine_display_name : '--------';
                })
                ->addColumn('shift', function ($row) {

                    $shiftName = 'Shift D';
                    $shiftStart = '00:00 AM';
                    $shiftEnd = '00:00 PM';
                    $deviceShift = json_decode($row->device->shift, true);

                    foreach ($deviceShift as $dsKey => $dsValue) {
                        $date = date('Y-m-d', strtotime($row->device_datetime));
                        $getShiftStart = date("Y-m-d H:i:s", strtotime(($date . " " . $dsValue['shift_start'])));
                        $getShiftEnd = date("Y-m-d H:i:s", strtotime(($date . " " . $dsValue['shift_end'])));
                        if (strtotime($row->device_datetime) >= strtotime($getShiftStart) && strtotime($row->device_datetime) < strtotime($getShiftEnd)) {
                            $shiftName = $dsValue['shift_name'];
                            $shiftStart = date('h:i A', strtotime($getShiftStart));
                            $shiftEnd = date('h:i A', strtotime($getShiftEnd));
                            break;
                        }
                    }

                    return "$shiftName ($shiftStart - $shiftEnd)";
                })
                ->rawColumns(['serial_no', 'user', 'device', 'node', 'machine', 'shift'])
                ->make(true);
        }

        $title = "Report | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('reports.index') => 'Report',
			'javascript: void(0)' => 'List',
		];

        return view('common.report.index', compact('title', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $modal_title = "Filter Report";
        $user_id = $request->user_id;
        $device_id = $request->device_id;
        $node_id = $request->node_id;
        $machine_id = $request->machine_id;
        $date = $request->date;

        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();

        $device = Device::when(!empty($user_id), function ($query) use ($user_id) {
            return $query->where('user_id', $user_id);
        })->where('status', 1)->get();

        $nodeMaster = NodeMaster::when(!empty($user_id), function ($query) use ($user_id) {
            return $query->where('user_id', $user_id);
        })->when(!empty($device_id), function ($query) use ($device_id) {
            return $query->where('device_id', $device_id);
        })->where('status', 1)->get();

        $machineMaster = MachineMaster::when(!empty($user_id), function ($query) use ($user_id) {
            return $query->where('user_id', $user_id);
        })->when(!empty($device_id), function ($query) use ($device_id) {
            return $query->where('device_id', $device_id);
        })->when(!empty($node_id), function ($query) use ($node_id) {
            return $query->where('node_id', $node_id);
        })->where('status', 1)->get();
        
        return response()->json([
            'statusCode' => 1,
            'html' => View::make("common.report.add_and_edit", compact('modal_title', 'user', 'device', 'nodeMaster', 'machineMaster', 'user_id', 'device_id', 'node_id', 'machine_id', 'date'))->render(),
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
            // echo "<pre>";
            // print_r($request->all());
            // die;

            $user_id = $request->user_id;
            $device_id = $request->device_id;
            $node_id = $request->node_id;
            
            $device = Device::when(!empty($user_id), function ($query) use ($user_id) {
                return $query->where('user_id', $user_id);
            })->where('status', 1)->get();

            $nodeMaster = NodeMaster::when(!empty($user_id), function ($query) use ($user_id) {
                return $query->where('user_id', $user_id);
            })->when(!empty($device_id), function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })->where('status', 1)->get();

            $machineMaster = MachineMaster::when(!empty($user_id), function ($query) use ($user_id) {
                return $query->where('user_id', $user_id);
            })->when(!empty($device_id), function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })->when(!empty($node_id), function ($query) use ($node_id) {
                return $query->where('node_id', $node_id);
            })->where('status', 1)->get();

            $response = [
                'statusCode' => 1,
                'device' => $device->isNotEmpty() ? $device->toArray() : [],
                'nodeMaster' => $nodeMaster->isNotEmpty() ? $nodeMaster->toArray() : [],
                'machineMaster' => $machineMaster->isNotEmpty() ? $machineMaster->toArray() : [],
            ];
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
