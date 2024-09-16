<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Auth;
use DataTables;
use App\Notifications\EmailVerification;
// use Illuminate\Support\Facades\Notification;
// use Illuminate\Auth\Notifications\VerifyEmail;

class UserController extends Controller
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
            $data = User::with('role', 'createdBy')->whereNotIn('role_id', [0])->orderBy('created_at','DESC')->get();
            $i = 0;
            return Datatables::of($data)
                ->addColumn('serial_no', function ($row) use (&$i) {
                    return $i += 1;
                })
                ->addColumn('role', function ($row) {
                    return !empty($row->role->name) ? $row->role->name : '--------';
                })
                ->addColumn('phone_number', function ($row) {
                    return '<a href="tel:' . $row->phone_number . '" class="text-theme-dark">' . $row->phone_number . '</a>';
                })
                ->addColumn('email', function ($row) {
                    return '<a href="mailto:' . $row->email . '" class="text-theme-dark">' . $row->email . '</a>';
                })
                ->addColumn('status', function ($row) {
                    return '<select class="form-control status-user" data-id="' . $row->id . '">
                                <option value="1" ' . ($row->status == 1 ? 'selected' : '') . '>Active</option>
                                <option value="2" ' . ($row->status == 2 ? 'selected' : '') . '>Pending</option>
                                <option value="3" ' . ($row->status == 3 ? 'selected' : '') . '>Inactive</option>
                            </select>';
                })
                ->addColumn('created_at', function ($row) {
                    return date('dS F Y', strtotime($row->created_at));
                })
                ->addColumn('created_by', function ($row) {
                    return !empty($row->createdBy->name) ? $row->createdBy->name : '--------';
                })
                ->addColumn('verified_at', function ($row) {
                    return $row->email_verified_at ? date('dS M Y', strtotime($row->email_verified_at)) : '--------';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="text-info btn btn-outline-secondary edit-user" data-id="' . $row->id . '" title="Edit">
                                <i class="icon-pencil7"></i>
                            </button>
                            &nbsp;&nbsp;
                            <button class="text-danger btn btn-outline-secondary delete-user" data-id="' . $row->id . '" title="Delete">
                                <i class="icon-trash"></i>
                            </button>';
                })
                ->rawColumns(['serial_no', 'role', 'phone_number', 'email', 'status', 'created_at', 'created_by', 'verified_at', 'action'])
                ->make(true);
        }

        $title = "User | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $breadcrumbs = [
			route('users.index') => 'User',
			'javascript: void(0)' => 'List',
		];
        return view('common.user.index', compact('title', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modal_title = "Add User";
        $role = Role::select('id', 'name')->where('status', 1)->orderBy('created_at','DESC')->get();
        return response()->json([
            'statusCode' => 1,
            'html' => View::make("common.user.add_and_edit", compact('modal_title', 'role'))->render(),
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
                'role_id' => ['required', 'numeric'],
                'phone_number' => ['required', 'numeric', 'digits_between:10,13', 'unique:users,phone_number'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 2;
            $data['created_by'] = Auth::user()->id;
            $user = User::create($data);
            if(!$user) {
                $response = array(
                    'statusCode' => 0,
                    'message' => "Something went wrong!",
                );
                return response()->json($response);
            }
            
            // Send verification email with cerdiential
            $customData = [
                'name' => $user->name,
                'email' => $data['email'],
                'password' => $request->password,
            ];
            $user->notify(new EmailVerification($customData));
            // $user->sendEmailVerificationNotification();
           
            $response = array(
                'statusCode' => 1,
                'message' => "User created successfully!",
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
            // Find the user to update
            $user = User::findOrFail($id);

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

            // Update the user with validated data
            $data = $validator->validated();
            $data['updated_by'] = Auth::user()->id;
            
            // Update user data
            $user->update($data);

            return response()->json([
                'statusCode' => 1,
                'message' => 'User status updated successfully!',
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
        $modal_title = "Update User";
        $role = Role::select('id', 'name')->where('status', 1)->orderBy('created_at','DESC')->get();
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'User not found!',
            ]);
        }

        return response()->json([
            'statusCode' => 1,
            'html' => View::make("common.user.add_and_edit", compact('modal_title', 'user', 'role'))->render(),
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
            // Find the user to update
            $user = User::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:3', 'max:50'],
                'role_id' => ['required', 'numeric'],
                'phone_number' => ['required', 'string', 'min:10', 'max:13', 'unique:users,phone_number,' . $user->id],
                // 'email' => ['required', 'email', 'unique:users,email,' . $user->id],
                // 'password' => ['sometimes', 'nullable', 'min:8'],
            ]);

            // Check for validation failure
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 0,
                    'message' => $validator->errors()->first(),
                ]);
            }

            // Update the user with validated data
            $data = $validator->validated();
            $data['updated_by'] = Auth::user()->id;

            // If password is provided, hash it
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                // If password is not set, remove it from the data to prevent update
                unset($data['password']);
            }
            // Update user data
            $user->update($data);
            
            return response()->json([
                'statusCode' => 1,
                'message' => 'User updated successfully!',
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
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'statusCode' => 0,
                'message' => 'User not found!',
            ]);
        }

        // Attempt to delete the user
        if ($user->delete()) {
            return response()->json([
                'statusCode' => 1,
                'message' => 'User deleted successfully!',
            ]);
        } else {
            return response()->json([
                'statusCode' => 0,
                'message' => 'Error occurred while deleting the user.',
            ]);
        }
    }
}