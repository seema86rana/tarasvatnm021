<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminURoleontroller;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
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
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::group(['namespace' => '', 'prefix' => '', 'middleware' => ['auth', 'verified']], function () {

    Route::resource('dashboard', CommonDashboardController::class);
});

Route::group(['namespace' => '', 'prefix' => 'common', 'middleware' => ['auth', 'verified']], function () {

    Route::resource('profile', CommonProfileController::class);
    Route::post('profile/{id}', [CommonProfileController::class, 'update']);

    Route::post('password/{id}', [CommonProfileController::class, 'password'])->name('password');

    Route::resource('nodes', CommonNodeController::class);
    Route::post('nodes/{id}', [CommonNodeController::class, 'update']);

    Route::resource('machines', CommonMachineController::class);
    Route::post('machines/{id}', [CommonMachineController::class, 'update']);
});

// superadmin
Route::group(['namespace' => '', 'prefix' => 'superadmin', 'middleware' => ['auth', 'verified', 'superadmin']], function () {

    Route::resource('users', SuperAdminUserController::class);
    Route::post('users/{id}', [SuperAdminUserController::class, 'update']);

    Route::resource('roles', SuperAdminURoleontroller::class);
    Route::post('roles/{id}', [SuperAdminURoleontroller::class, 'update']);
});
// admin
Route::group(['namespace' => '', 'prefix' => 'admin', 'middleware' => ['auth', 'verified', 'admin']], function () {
});
// user
Route::group(['namespace' => '', 'prefix' => 'user', 'middleware' => ['auth', 'verified', 'user']], function () {
});
