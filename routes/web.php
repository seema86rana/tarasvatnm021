<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\RoleController as CommonRoleontroller;
use App\Http\Controllers\Common\UserController as CommonUserController;
use App\Http\Controllers\Common\DashboardController as CommonDashboardController;
use App\Http\Controllers\Common\ProfileController as CommonProfileController;
use App\Http\Controllers\Common\DeviceController as CommonDeviceController;
use App\Http\Controllers\Common\NodeController as CommonNodeController;
use App\Http\Controllers\Common\MachineController as CommonMachineController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
});
// auth
Auth::routes(['verify' => true]);
// common
Route::group(['namespace' => '', 'prefix' => 'common', 'middleware' => ['auth', 'verified', 'permission']], function () {

    Route::resource('dashboard', CommonDashboardController::class);

    Route::resource('profile', CommonProfileController::class);
    Route::post('profile/{id}', [CommonProfileController::class, 'update'])->name('profile.updates');
    Route::post('profile/password/{id}', [CommonProfileController::class, 'password'])->name('profile.password');

    Route::resource('users', CommonUserController::class);
    Route::post('users/{id}', [CommonUserController::class, 'update'])->name('users.updates');;

    Route::resource('roles', CommonRoleontroller::class);
    Route::post('roles/{id}', [CommonRoleontroller::class, 'update'])->name('roles.updates');;

    Route::resource('devices', CommonDeviceController::class);
    Route::post('devices/{id}', [CommonDeviceController::class, 'update'])->name('devices.updates');;

    Route::resource('nodes', CommonNodeController::class);
    Route::post('nodes/{id}', [CommonNodeController::class, 'update'])->name('nodes.updates');;

    Route::resource('machines', CommonMachineController::class);
    Route::post('machines/{id}', [CommonMachineController::class, 'update'])->name('machines.updates');;
});
