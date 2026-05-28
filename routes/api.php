<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);

    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'changeStatus']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
