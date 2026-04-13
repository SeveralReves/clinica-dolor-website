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

        // Gestión de disponibilidad custom (admin)
        Route::get('specialists/{specialist}/custom-availabilities', [SpecialistController::class, 'getCustomAvailabilities']);
        Route::post('specialists/{specialist}/custom-availabilities', [SpecialistController::class, 'storeCustomAvailability']);
        Route::delete('specialists/{specialist}/custom-availabilities/{custom}', [SpecialistController::class, 'destroyCustomAvailability']);

        // Slots ocupados (uso interno admin)
        Route::get('appointments/busy-slots', [AppointmentController::class, 'getBusySlots']);
    });
});

// Rutas públicas
Route::get('appointments', [AppointmentController::class, 'index']);
Route::post('appointments', [AppointmentController::class, 'store']);
Route::get('appointments/status', [AppointmentController::class, 'checkStatus']);

// Disponibilidad para el formulario de reserva (público)
Route::get('appointments/available-slots', [AppointmentController::class, 'getAvailableSlots']);