<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Menu;

if (!function_exists('menuAccesspermission')) {
    function menuAccesspermission() {
        try {
            $name = request()->route()->getName();
            $param = explode(".", $name)[0] ?? '';
            $defaultPermission = ['profile', 'birdview'];
            $restrictedPermission = [];
            if (Auth::user()->role_id == 0) {
                $restrictedPermission[] = "birdview";
            }
            if (Auth::user()->role_id == 0 && !in_array($param, $restrictedPermission)) {
                return true;
            }
            $user = user::with('role')->where('role_id', Auth::user()->role_id)->first();
            if(!$user->role->permission) {
                return false;
            }
            $permission = json_decode($user->role->permission, true);
            if((in_array($param, $permission) || in_array($param, $defaultPermission)) && !in_array($param, $restrictedPermission)) {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('userRole')) {
    function userRole() {
        try {
            $user = user::with('role')->where('id', Auth::user()->id)->first();

            if(isset($user->role_id) && $user->role_id == 0) {
                return 'SuperAdmin';
            }
            if(isset($user->role->name)) {
                return $user->role->name;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('userMenuList')) {
    function userMenuList() {
        try {
            if(userRole() == "SuperAdmin") {
                $menu = Menu::where('status', 1)
                            ->where('parent_id', 0)
                            ->with(['subMenu' => function($query) {
                                $query
                                    ->where('status', 1) // Filter submenus by permission
                                    ->orderBy('position', 'ASC');
                            }])
                            ->orderBy('position', 'ASC')
                            ->get();
            }
            else {
                $user = user::with('role')->where('role_id', Auth::user()->role_id)->first();
                if(!$user->role->permission) {
                    return false;
                }
                $permission = json_decode($user->role->permission, true);
                $menu = Menu::whereIn('route', $permission)
                            ->where('status', 1)
                            ->where('parent_id', 0)
                            ->with(['subMenu' => function($query) use ($permission) {
                                $query
                                    ->whereIn('route', $permission)->where('status', 1) // Filter submenus by permission
                                    ->orderBy('position', 'ASC');
                            }])
                            ->orderBy('position', 'ASC')
                            ->get();
            }
            return $menu->toArray();
        } catch (\Throwable $th) {
            return false;
        }
    }
}
