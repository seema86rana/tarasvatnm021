<?php

namespace App\Http\Controllers\Backend;

use Exception;
use Carbon\Carbon;
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
            $select_shift = $request->select_shift;
            $select_shift_day = $request->select_shift_day;
            $node_id = $request->node_id;
            $machine_id = $request->machine_id;
            $date = $request->date;
            $start = $request->start;
            $length = $request->length;

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
                
            if ($date) {
                $startDate = Carbon::createFromFormat('m/d/Y', $date);
                $endDate = $startDate->copy()->addDay(); // Next day for shifts crossing midnight

                if ($startTime && $endTime) {
                    // If shift ends the next day, adjust the end date
                    $modifyEndDate = ($endDay == '2') ? $endDate : $startDate;

                    $start_date = Carbon::createFromFormat('m/d/Y H:i:s', "{$date} {$startTime}")
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

            $totalRecord = $query->count();
            $data = $query->orderBy('id','ASC');

            $i = $start;
            return DataTables::of($data)
                ->setTotalRecords($totalRecord) // Important for pagination with large data
                ->setFilteredRecords($totalRecord) // If you implement search, update this dynamically
                // ->addColumn('serial_no', function ($row) use (&$i) {
                //     return $i += 1;
                // })
                ->addColumn('log_id', function ($row) {
                    return $row->id;
                })
                ->addColumn('device', function ($row) {
                    return !empty($row->machine->node->device->name) ? $row->machine->node->device->name : '--------';
                })
                ->addColumn('machine', function ($row) {
                    return !empty($row->machine->name) ? $row->machine->name : '--------';
                })
                ->addColumn('total_running', function ($row) {
                    return !empty($row->total_running) ? (int) $row->total_running . ' <span class="small-text">(min)</span>' : '--------';
                })
                ->addColumn('total_time', function ($row) {
                    return !empty($row->total_time) ? (int) $row->total_time . ' <span class="small-text">(min)</span>' : '--------';
                })
                ->addColumn('efficiency', function ($row) {
                    return !empty($row->efficiency) ? (float) $row->efficiency . '%' : '--------';
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
                ->addColumn('last_stop', function ($row) {
                    return !empty($row->last_stop) ? (int) $row->last_stop . ' <span class="small-text">(min)</span>' : '--------';
                })
                ->addColumn('last_running', function ($row) {
                    return !empty($row->last_running) ? (int) $row->last_running . ' <span class="small-text">(min)</span>' : '--------';
                })
                ->addColumn('no_of_stoppage', function ($row) {
                    return !empty($row->no_of_stoppage) ? $row->no_of_stoppage : 0;
                })
                ->addColumn('mode', function ($row) {
                    return !empty($row->status) ? $row->status : 0;
                })
                ->addColumn('speed', function ($row) {
                    return !empty($row->speed) ? $row->speed : 0;
                })
                ->addColumn('pick', function ($row) {
                    return !empty($row->machine_log->pick) ? self::formatIndianNumber($row->machine_log->pick) : 0;
                })
                ->rawColumns(['log_id', 'device', 'machine', 'total_running', 'total_time', 'efficiency', 'shift', 'deviceDatetime', 'machineDatetime', 'last_stop', 'last_running', 'no_of_stoppage', 'mode', 'speed', 'pick'])
                ->make(true);
        }

        $title = "Report | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('view-reports.index') => 'Report',
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

    private static function formatIndianNumber($num) {
        return number_format($num, 0, '.', ',');
    }
}
