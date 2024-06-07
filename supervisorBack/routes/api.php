<?php
use App\Http\Controllers\ScriptController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/scripts/start', [ScriptController::class, 'start']);
Route::post('/scripts/stop', [ScriptController::class, 'stop']);
Route::post('/scripts/restart', [ScriptController::class, 'restart']);
Route::post('/scripts/status', [ScriptController::class, 'status']);



Route::post('/scripts/start/all', [ScriptController::class, 'startAll']);
Route::post('/scripts/stop/all', [ScriptController::class, 'stopAll']);
Route::post('/scripts/restart/all', [ScriptController::class, 'restartAll']);

Route::get('/scripts/download', [ScriptController::class, 'downloadScript']);

Route::post('/scripts/sendNumber', [ScriptController::class, 'sendNumber']);

Route::post('/download-logs', [ScriptController::class, 'downloadLogs']);

Route::post('/show-log', [ScriptController::class, 'showLog']);
