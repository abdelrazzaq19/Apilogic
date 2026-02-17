<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

    Route::get('/event', [EventController::class, 'index']);

    Route::get('/event/{id}', [EventController::class, 'show']);

    Route::group(['middleware' => ['role:admin']], function () {
        Route::post('/event', [EventController::class, 'store']);
        Route::post('/event/{eventId}', [EventController::class, 'update']);
        Route::delete('event/{eventId}', [EventController::class, 'delete']);
    });

    Route::group(['middleware' => ['role:attendee']], function () {
        // 
    });
});

Route::group([], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
