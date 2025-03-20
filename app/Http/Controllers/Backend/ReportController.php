<?php

namespace App\Http\Controllers\Backend;

use Exception;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use Illuminate\Http\Request;
use App\Models\MachineMaster;
use App\Models\TempMachineStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

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

            $query = TempMachineStatus::with([])
                ->when(!empty($user_id), function ($query) use ($user_id) {
                    return $query->whereHas('machine.node.device.user', function($q) use ($user_id) {
                        $q->where('user_id', $user_id);
                    });
                })
                ->when(!empty($device_id), function ($query) use ($device_id) {
                    return $query->whereHas('machine.node.device', function($q) use ($device_id) {
                        $q->where('device_id', $device_id);
                    });
                })
                ->when(!empty($node_id), function ($query) use ($node_id) {
                    return $query->whereHas('machine.node', function($q) use ($node_id) {
                        $q->where('node_id', $node_id);
                    });
                })
                ->when(!empty($machine_id), function ($query) use ($machine_id) {
                    return $query->whereHas('machine', function($q) use ($machine_id) {
                        $q->where('machine_id', $machine_id);
                    });
                })
                ->when(!empty($date), function ($query) use ($date) {
                    return $query->whereDate('created_at', date('Y-m-d', strtotime($date)));
                });
                
            $totalRecord = $query->count();
            $data = $query->orderBy('id','ASC');

            $i = $start;
            return DataTables::of($data)
                ->setTotalRecords($totalRecord) // Important for pagination with large data
                ->setFilteredRecords($totalRecord) // If you implement search, update this dynamically
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('user', function ($row) {
                    return !empty($row->machine->node->device->user->name) ? $row->machine->node->device->user->name : '--------';
                })
                ->addColumn('device', function ($row) {
                    return !empty($row->machine->node->device->name) ? $row->machine->node->device->name : '--------';
                })
                ->addColumn('node', function ($row) {
                    return !empty($row->machine->node->name) ? $row->machine->node->name : '--------';
                })
                ->addColumn('machine', function ($row) {
                    return !empty($row->machine->name) ? $row->machine->name : '--------';
                })
                ->addColumn('shift', function ($row) {
                    $shiftStart = date('d/m/Y h:i A', strtotime($row->shift_start_datetime));
                    $shiftEnd = date('d/m/Y h:i A', strtotime($row->shift_end_datetime));
                    $shiftName = 'Shift';
                    return "{$shiftName} ({$shiftStart} - {$shiftEnd})";
                })
                ->addColumn('deviceDatetime', function ($row) {
                    return date('d/m/Y H:i:s', strtotime($row->device_datetime));
                })
                ->addColumn('machineDatetime', function ($row) {
                    return date('d/m/Y H:i:s', strtotime($row->machine_datetime));
                })
                ->addColumn('mode', function ($row) {
                    return !empty($row->status) ? $row->status : 0;
                })
                ->addColumn('pick', function ($row) {
                    return !empty($row->machine_log->pick) ? $row->machine_log->pick : 0;
                })
                ->rawColumns(['serial_no', 'user', 'device', 'node', 'machine', 'shift', 'deviceDatetime', 'machineDatetime', 'mode', 'pick'])
                ->make(true);
        }

        $title = "Report | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('reports.index') => 'Report',
			'javascript: void(0)' => 'List',
		];

        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();
        $device = Device::where('status', 1)->get();
        $nodeMaster = NodeMaster::where('status', 1)->get();
        $machineMaster = MachineMaster::where('status', 1)->get();

        return view('backend.report.index', compact('title', 'breadcrumbs', 'user', 'device', 'nodeMaster', 'machineMaster'));
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

        $nodeMaster = NodeMaster::when(!empty($device_id), function ($query) use ($device_id) {
            return $query->where('device_id', $device_id);
        })->where('status', 1)->get();

        $machineMaster = MachineMaster::when(!empty($node_id), function ($query) use ($node_id) {
            return $query->where('node_id', $node_id);
        })->where('status', 1)->get();
        
        return response()->json([
            'statusCode' => 1,
            'html' => View::make("backend.report.add_and_edit", compact('modal_title', 'user', 'device', 'nodeMaster', 'machineMaster', 'user_id', 'device_id', 'node_id', 'machine_id', 'date'))->render(),
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

            $nodeMaster = NodeMaster::when(!empty($device_id), function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })->where('status', 1)->get();

            $machineMaster = MachineMaster::when(!empty($node_id), function ($query) use ($node_id) {
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
