<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SpecialistController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceBookingController;
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

        // Gestión de citas (admin)
        Route::put('appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::post('appointments/{appointment}/reminder', [AppointmentController::class, 'sendReminder']);

        // Servicios/Salas (admin)
        Route::apiResource('services', ServiceController::class);

        // Custom availabilities de servicios (admin)
        Route::get('services/{service}/custom-availabilities', [ServiceController::class, 'getCustomAvailabilities']);
        Route::post('services/{service}/custom-availabilities', [ServiceController::class, 'storeCustomAvailability']);
        Route::delete('services/{service}/custom-availabilities/{custom}', [ServiceController::class, 'destroyCustomAvailability']);

        // Reservas de servicios (admin: CRUD)
        Route::get('service-bookings', [ServiceBookingController::class, 'index']);
        Route::put('service-bookings/{serviceBooking}', [ServiceBookingController::class, 'update']);
        Route::delete('service-bookings/{serviceBooking}', [ServiceBookingController::class, 'destroy']);

        // Galería (admin)
        Route::post('gallery', [GalleryController::class, 'store']);
        Route::post('gallery/{galleryItem}', [GalleryController::class, 'update']); // POST + _method=PUT para multipart
        Route::delete('gallery/{galleryItem}', [GalleryController::class, 'destroy']);
        Route::post('gallery-reorder', [GalleryController::class, 'reorder']);
    });
});

// Galería pública
Route::get('gallery', [GalleryController::class, 'index']);

// Rutas públicas
Route::get('appointments', [AppointmentController::class, 'index']);
Route::post('appointments', [AppointmentController::class, 'store']);
Route::get('appointments/status', [AppointmentController::class, 'checkStatus']);

// Disponibilidad para el formulario de reserva (público)
Route::get('appointments/available-slots', [AppointmentController::class, 'getAvailableSlots']);

// Reservas de servicios (público: crear + consultar slots)
Route::post('service-bookings', [ServiceBookingController::class, 'store']);
Route::get('service-bookings/available-slots', [ServiceBookingController::class, 'getAvailableSlots']);