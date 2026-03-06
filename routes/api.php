<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index']);
    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store']);
    Route::get('/tickets/{ticket}/comments', [\App\Http\Controllers\TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [\App\Http\Controllers\TicketCommentController::class, 'store']);
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show']);

    Route::patch('/tickets/{ticket}/assign', [\App\Http\Controllers\TicketController::class, 'assign']);
    Route::patch('/tickets/{ticket}/status', [\App\Http\Controllers\TicketController::class, 'changeStatus']);
});
