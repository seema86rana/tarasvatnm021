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

Route::post('packet', [ApiController::class, 'packet'])->name('packet');
// Route::get('packet-testing', [ApiController::class, 'packetTest'])->name('packet.test');
Route::any('run-command/{type}/{mig}', [ApiController::class, 'runCommand'])->name('run.command');
