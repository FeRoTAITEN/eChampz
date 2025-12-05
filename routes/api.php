<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API Version 1 - All routes prefixed with /api/v1
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Public routes (no authentication required)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/roles', [AuthController::class, 'roles']);

    // Password Reset routes
    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'forgotPassword']);
        Route::post('/verify-code', [PasswordResetController::class, 'verifyCode']);
        Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
    });

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth routes
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        
        // Gamer-only routes
        Route::middleware('role:gamer')->prefix('gamer')->group(function () {
            // Add gamer-specific routes here
        });

        // Recruiter-only routes
        Route::middleware('role:recruiter')->prefix('recruiter')->group(function () {
            // Add recruiter-specific routes here
        });
        
    });
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});
