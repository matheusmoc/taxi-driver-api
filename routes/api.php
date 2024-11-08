<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RideController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/passengers', [PassengerController::class, 'store']);
Route::post('/drivers', [DriverController::class, 'store']);
Route::post('/rides', [RideController::class, 'store']);
Route::patch('/rides/{id}', [RideController::class, 'updateStatus']);
Route::get('/rides/{id}', [RideController::class, 'show']);
