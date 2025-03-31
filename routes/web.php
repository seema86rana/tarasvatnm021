<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\RoleController as BackendRoleontroller;
use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Controllers\Backend\DashboardController as BackendDashboardController;
use App\Http\Controllers\Backend\ProfileController as BackendProfileController;
use App\Http\Controllers\Backend\DeviceController as BackendDeviceController;
use App\Http\Controllers\Backend\ReportController as BackendReportController;
use App\Http\Controllers\Backend\ClearLogController as BackendClearLogController;
use App\Http\Controllers\FrontEnd\BirdViewController as FrontEndBirdViewController;
use App\Http\Controllers\FrontEnd\HomeController as FrontEndHomeController;
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

Route::get('/', [FrontEndHomeController::class, 'index'])->name('home');

Route::get('/birdviews', [FrontEndBirdViewController::class, 'index'])->name('bird.view');
// Route::get('/', function () {
//     return redirect()->route('dashboard.index');
// });

Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
});

// auth
Auth::routes(['verify' => true]);

// profile
Route::group(['middleware' => ['auth']], function () {
    Route::resource('profile', BackendProfileController::class);
    Route::post('profile/{id}', [BackendProfileController::class, 'update'])->name('profile.updates');
    Route::post('profile/password/{id}', [BackendProfileController::class, 'password'])->name('profile.password');
});

// birdview
Route::resource('birdview', FrontEndBirdViewController::class);

// backend
Route::group(['namespace' => '', 'prefix' => 'backend', 'middleware' => ['auth', 'verified', 'permission']], function () {

    Route::resource('dashboard', BackendDashboardController::class);

    Route::resource('users', BackendUserController::class);
    Route::post('users/{id}', [BackendUserController::class, 'update'])->name('users.updates');

    Route::resource('roles', BackendRoleontroller::class);
    Route::post('roles/{id}', [BackendRoleontroller::class, 'update'])->name('roles.updates');

    Route::resource('devices', BackendDeviceController::class);
    Route::post('devices/{id}', [BackendDeviceController::class, 'update'])->name('devices.updates');

    Route::resource('view-reports', BackendReportController::class);
    Route::post('view-reports/{id}', [BackendReportController::class, 'update'])->name('view-reports.updates');

    Route::resource('clear-reports', BackendClearLogController::class);
    Route::post('clear-reports/{id}', [BackendClearLogController::class, 'update'])->name('clear-reports.updates');
});
