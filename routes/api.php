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
Route::any('send-report', [ApiController::class, 'sendReport'])->name('send.report');
Route::any('generate-report/{filter}/{format}/{userId}', [ApiController::class, 'generateReport'])->name('generate.report');
Route::any('run-command', [ApiController::class, 'runCommand'])->name('run.command');
