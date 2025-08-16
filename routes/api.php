<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\Api\EbookController;

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
    Route::get('/with-documentation', [EventController::class, 'eventsWithDocumentation']);
    Route::get('/{event}', [EventController::class, 'show']);
    
    // Event Documentation Routes
    Route::get('/{event}/documentation', [EventController::class, 'getDocumentation']);
    Route::post('/{event}/documentation/upload', [EventController::class, 'uploadDocumentation']);
    Route::put('/{event}/documentation/description', [EventController::class, 'updateDocumentationDescription']);
    Route::delete('/{event}/documentation', [EventController::class, 'deleteDocumentation']);
});

// Public Event Registration Routes
Route::prefix('event-registrations')->group(function () {
    Route::post('/', [EventRegistrationController::class, 'store']);
    Route::get('/user/{phone}', [EventRegistrationController::class, 'userRegistrations']);
    Route::delete('/cancel/{phone}/{eventId}', [EventRegistrationController::class, 'cancel']);
});

// Public E-book Routes
Route::prefix('ebooks')->group(function () {
    Route::get('/', [EbookController::class, 'index']);
    Route::get('/popular', [EbookController::class, 'popular']);
    Route::get('/free', [EbookController::class, 'free']);
    Route::get('/with-audiobook', [EbookController::class, 'withAudiobook']);
    Route::get('/{ebook}', [EbookController::class, 'show']);
    Route::get('/{ebook}/statistics', [EbookController::class, 'statistics']);
    
    // E-book Management Routes (Admin only - will add auth middleware later)
    Route::post('/', [EbookController::class, 'store']);
    Route::put('/{ebook}', [EbookController::class, 'update']);
    Route::delete('/{ebook}', [EbookController::class, 'destroy']);
    
    // Audiobook Management Routes
    Route::post('/{ebook}/audiobook/upload', [EbookController::class, 'uploadAudiobook']);
    Route::delete('/{ebook}/audiobook', [EbookController::class, 'deleteAudiobook']);
    
    // User Interaction Routes
    Route::post('/{ebook}/interact', [EbookController::class, 'recordInteraction']);
});
