<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('packet', [ApiController::class, 'packet'])->name('packet');
Route::any('run-command', [ApiController::class, 'runCommand'])->name('run.command');
Route::any('delete-report/{did}', [ApiController::class, 'deleteReport'])->name('delete.report');


Route::group(['prefix' => 'machine'], function () {
    Route::any('report/{type}', [ApiController::class, 'report'])->name('machine.report');
    Route::post('generate-report', [ApiController::class, 'generateReport'])->name('machine.generate_report');
    Route::post('send-report', [ApiController::class, 'sendReport'])->name('machine.send_report');
});

// http://127.0.0.1:8000/api/machine/report/machine_status
// http://127.0.0.1:8000/api/machine/report/machine_stop
