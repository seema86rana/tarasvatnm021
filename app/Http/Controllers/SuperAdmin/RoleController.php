<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use DataTables;

class RoleController extends Controller
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
            $data = Role::orderBy('created_at','DESC')->get();
            $i = 0;
            return Datatables::of($data)
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('status', function ($row) {
                    return '<select class="form-control status-role" data-id="' . $row->id . '">
                                <option value="1" ' . ($row->status == 1 ? 'selected' : '') . '>Active</option>
                                <option value="2" ' . ($row->status == 2 ? 'selected' : '') . '>Inactive</option>
                            </select>';
                })
                ->addColumn('created_at', function ($row) {
                    return date('dS F Y', strtotime($row->created_at));
                })
                ->addColumn('action', function ($row) {
                    return (!in_array($row->id, [1, 2, 3])) ? '<button class="text-info btn btn-outline-secondary edit-role" data-id="' . $row->id . '" title="Edit">
                                <i class="icon-pencil7"></i>
                            </button>
                            &nbsp;&nbsp;
                            <button class="text-danger btn btn-outline-secondary delete-role" data-id="' . $row->id . '" title="Delete">
                                <i class="icon-trash"></i>
                            </button>' : '<button class="text-info btn btn-outline-secondary" disabled title="Edit">
                                <i class="icon-pencil7"></i>
                            </button>
                            &nbsp;&nbsp;
                            <button class="text-danger btn btn-outline-secondary" disabled title="Delete">
                                <i class="icon-trash"></i>
                            </button>';
                })
                ->rawColumns(['serial_no', 'status', 'created_at', 'action'])
                ->make(true);
        }

        $title = "Role | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('roles.index') => 'Role',
			'javascript: void(0)' => 'List',
		];
        return view('superadmin.role.index', compact('title', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modal_title = "Add Role";
        return response()->json([
            'statusCode' => 1,
            'html' => View::make("superadmin.role.add_and_edit", compact('modal_title'))->render(),
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
                'name' => ['required', 'string', 'min:3', 'max:50'],
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
            $data['status'] = 1;
            $role = Role::create($data);
            if(!$role) {
                $response = array(
                    'statusCode' => 0,
                    'message' => "Something went wrong!",
                );
                return response()->json($response);
            } 
           
            $response = array(
                'statusCode' => 1,
                'message' => "Role created successfully!",
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
            // Find the role to update
            $role = Role::findOrFail($id);

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

            // Update the role with validated data
            $data = $validator->validated();

            // Update role data
            $role->update($data);

            return response()->json([
                'statusCode' => 1,
                'message' => 'Role status updated successfully!',
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
        $modal_title = "Update Role";
        $role = Role::where('id', $id)->first();
        if (!$role) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Role not found!',
            ]);
        }

        return response()->json([
            'statusCode' => 1,
            'html' => View::make("superadmin.role.add_and_edit", compact('modal_title', 'role'))->render(),
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
            // Find the role to update
            $role = Role::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:3', 'max:50'],               
            ]);

            // Check for validation failure
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 0,
                    'message' => $validator->errors()->first(),
                ]);
            }

            // Update the role with validated data
            $data = $validator->validated();

            // Update role data
            $role->update($data);
          
            return response()->json([
                'statusCode' => 1,
                'message' => 'Role updated successfully!',
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
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Role not found!',
            ]);
        }

        // Attempt to delete the role
        if ($role->delete()) {
            return response()->json([
                'statusCode' => 1,
                'message' => 'Role deleted successfully!',
            ]);
        } else {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Error occurred while deleting the role.',
            ]);
        }
    }
}
