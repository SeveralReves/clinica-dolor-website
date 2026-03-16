<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SpecialistController;
use App\Http\Controllers\Api\AppointmentController;
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
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:admin,superadmin'])->group(function () {
        
        Route::apiResource('users', UserController::class);

        Route::apiResource('specialists', SpecialistController::class);

        // Citas y Disponibilidad (Landing/App)
        Route::get('appointments/busy-slots', [AppointmentController::class, 'getBusySlots']);
    });
});

Route::post('appointments', [AppointmentController::class, 'store']);
Route::get('appointments/status', [AppointmentController::class, 'checkStatus']);