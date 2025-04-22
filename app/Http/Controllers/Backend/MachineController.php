<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MachineMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Yajra\DataTables\Facades\DataTables;

class MachineController extends Controller
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

            $searchValue = isset($request->search['value']) ? $request->search['value'] : '';
            $orderColumn = isset($request->order[0]['column']) ? (int) $request->order[0]['column'] : 0;
            $orderDirection = isset($request->order[0]['dir']) ? $request->order[0]['dir'] : 'ASC';
            $orderColumnSort = ['id', 'name', 'display_name', 'priority', 'status', 'created_at'];

            $data = MachineMaster::whereHas('node.device', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                return $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('display_name', 'like', '%' . $searchValue . '%');
            });

            if (!empty($orderDirection) && array_key_exists($orderColumn, $orderColumnSort) && !empty($orderColumnSort[$orderColumn])) {
                $data->orderBy($orderColumnSort[$orderColumn], $orderDirection);
            } else {
                $data->orderBy('id', 'ASC');
            }

            $i = 0;
            return Datatables::of($data)
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('name', function ($row) {
                    return !empty($row->name) ? $row->name : '--------';
                })
                ->addColumn('display_name', function ($row) {
                    return !empty($row->display_name) ? $row->display_name : $row->name;
                })
                ->addColumn('priority', function ($row) {
                    return !empty($row->priority && $row->priority == 1) ? 'Yes' : 'No';
                })
                ->addColumn('status', function ($row) {
                    return (!empty($row->status) && $row->status == 1) ? 'Active' : 'Inactive';
                })
                ->addColumn('created_at', function ($row) {
                    return date('d/m/Y', strtotime($row->created_at));
                })
                ->addColumn('action', function ($row) {
                    return '<button class="text-info btn btn-outline-secondary edit-machine" data-id="' . $row->id . '" title="Edit">
                                <i class="icon-pencil7"></i>
                            </button>';
                })
                ->rawColumns(['serial_no', 'name', 'display_name', 'priority', 'status', 'created_at', 'action'])
                ->make(true);
        }

        $title = "Machine | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('machines.index') => 'Machine',
			'javascript: void(0)' => 'List',
		];
        return view('backend.machine.index', compact('title', 'breadcrumbs'));
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
        //
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
            // Find the machine to update
            $machine = MachineMaster::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'display_name' => ['required'],
            ]);

            // Check for validation failure
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 0,
                    'message' => $validator->errors()->first(),
                ]);
            }

            // Update the machine with validated data
            $data = $validator->validated();
            
            // Update machine data
            $machine->update($data);

            return response()->json([
                'statusCode' => 1,
                'message' => 'Machine status updated successfully!',
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
        $modal_title = "Update Machine";
        $user = User::select('id', 'name')->whereNotIn('role_id', [0])->where('status', 1)->orderBy('created_at','DESC')->get();
        $machine = MachineMaster::where('id', $id)->first();
        if (!$machine) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Machine not found!',
            ]);
        }

        return response()->json([
            'statusCode' => 1,
            'html' => View::make("backend.machine.add_and_edit", compact('modal_title', 'machine', 'user'))->render(),
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
            // Find the machine to update
            $machine = MachineMaster::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'display_name' => ['required', 'string'],
                'priority' => ['nullable', 'integer'],
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                $response = array(
                    'statusCode' => 0,
                    'message' => $error,
                );
                return response()->json($response);
            }

            $data = $validator->validated();

            // Update machine data
            $machine->update($data);
            return response()->json([
                'statusCode' => 1,
                'message' => 'Machine updated successfully!',
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
        //
    }
}
