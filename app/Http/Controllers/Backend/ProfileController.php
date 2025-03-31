<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Profile | " . ucwords(str_replace("_", " ", config('app.name', 'Laravel')));
        $role = Role::select('id', 'name')->where('status', 1)->orderBy('created_at','DESC')->get();
        $user = Auth::user();
        $breadcrumbs = [
			'javascript: void(0)' => 'profile',
		];
        return view('backend.profile.index', compact('title', 'breadcrumbs', 'role', 'user'));
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
    public function edit(Request $request, $id)
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
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:3', 'max:50'],
                'phone_number' => ['required', 'numeric', 'digits_between:10,13', 'unique:users,phone_number,' . $user->id],
                'company_name' => ['required', 'string', 'min:3', 'max:50'],
                'gst_number' => ['required', 'string', 'unique:users,gst_number,' . $user->id],
                'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Image validation
                'address' => ['required', 'string'],
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $validator->validated();
            $data['updated_by'] = Auth::user()->id;

            if ($request->hasFile('profile_image')) {

                if ($user->profile_image && file_exists(public_path('assets/profile_image/' . $user->profile_image))) {
                    unlink(public_path('assets/profile_image/' . $user->profile_image)); // Delete the old file
                }

                $image = $request->file('profile_image');
                $imageName = time() . '_' . $image->getClientOriginalName(); //$image->getClientOriginalExtension();
                $image->move(public_path('assets/profile_image'), $imageName);
                $data['profile_image'] = $imageName;
            }
            
            $user->update($data);
            if(!$user) {
                return redirect()->back()->with('error', "Something went wrong!");
            }

            return redirect()->back()->with('success', "Profile update successfully!");

        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
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

    public function password(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
    
            // Validate the password and its confirmation
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
    
            // If validation fails, redirect back with errors and input
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
    
            // Hash the new password and prepare for updating
            $user->password = Hash::make($request->input('password'));
            $user->updated_by = Auth::user()->id;
            
            // Save the updated user record
            if ($user->save()) {
                return redirect()->back()->with('success', "Password updated successfully!");
            }
    
            return redirect()->back()->with('error', "Something went wrong!");
    
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
    }    
}
