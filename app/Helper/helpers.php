<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;

if (!function_exists('menuAccesspermission')) {
    function menuAccesspermission($param) {

        // Role and Permission
        $permission = [
            'SuperAdmin' => ['dashboard', 'users', 'devices', 'nodes', 'machines', 'roles'],
            'Admin' => ['dashboard', 'devices', 'nodes', 'machines'],
            'User' => ['dashboard', 'nodes', 'machines'],
        ];
        
        if(userRole() && isset($permission[userRole()]) && in_array($param, $permission[userRole()])) {
            return true;
        }
        return false;
    }
}

if (!function_exists('userRole')) {
    function userRole() {
        try {
            $role = user::with('role')->where('id', Auth::user()->role_id)->first();
            if(isset($role->role->name)) {
                return $role->role->name;
            } else {
                return '';
            }
        } catch (\Throwable $th) {
            return '';
        }
    }
}
