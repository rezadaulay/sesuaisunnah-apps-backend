<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\Api\EbookController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DonationSettingsController;

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

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/check-user-status', [AuthController::class, 'checkUserStatus']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::get('/activity-history', [AuthController::class, 'activityHistory']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// Public Event Routes (Read-only for frontend)
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/upcoming', [EventController::class, 'upcoming']);
    Route::get('/featured', [EventController::class, 'featured']);
    Route::get('/all-featured', [EventController::class, 'allFeatured']);
    Route::get('/with-documentation', [EventController::class, 'eventsWithDocumentation']);
    Route::get('/{event}', [EventController::class, 'show']);

    // Event Documentation Routes (Read-only)
    Route::get('/{event}/documentation', [EventController::class, 'getDocumentation']);
});

// Public Event Registration Routes
Route::prefix('event-registrations')->group(function () {
    Route::post('/', [EventRegistrationController::class, 'store']);
    Route::get('/user/{phone}', [EventRegistrationController::class, 'userRegistrations']);
    Route::delete('/cancel/{phone}/{eventId}', [EventRegistrationController::class, 'cancel']);
});

// Public E-book Routes (Read-only for frontend)
Route::prefix('ebooks')->group(function () {
    Route::get('/', [EbookController::class, 'index']);
    Route::get('/popular', [EbookController::class, 'popular']);
    Route::get('/free', [EbookController::class, 'free']);
    Route::get('/with-audiobook', [EbookController::class, 'withAudiobook']);
    Route::get('/{ebook}', [EbookController::class, 'show']);
    Route::get('/{ebook}/statistics', [EbookController::class, 'statistics']);

    // User Interaction Routes (for tracking user engagement)
    Route::post('/{ebook}/interact', [EbookController::class, 'recordInteraction']);
});

// Public Donation Settings Routes (Read-only for frontend)
Route::prefix('donation-settings')->group(function () {
    Route::get('/', [DonationSettingsController::class, 'index']);
    Route::get('/active', [DonationSettingsController::class, 'getActive']);
});
