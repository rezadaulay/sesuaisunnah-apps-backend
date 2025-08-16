<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;

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

// Public Event Routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/upcoming', [EventController::class, 'upcoming']);
    Route::get('/featured', [EventController::class, 'featured']);
    Route::get('/{event}', [EventController::class, 'show']);
});

// Public Event Registration Routes
Route::prefix('event-registrations')->group(function () {
    Route::post('/', [EventRegistrationController::class, 'store']);
    Route::get('/user/{phone}', [EventRegistrationController::class, 'userRegistrations']);
    Route::delete('/cancel/{phone}/{eventId}', [EventRegistrationController::class, 'cancel']);
});
