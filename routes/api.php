<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\PostController;
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
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/roles', [AuthController::class, 'roles']);

    // Password Reset routes
    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])->middleware('throttle:5,1');
        Route::post('/reset', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1');
    });

    // Authenticated routes (but email may not be verified)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth routes
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        // Email verification routes
        Route::prefix('email')->group(function () {
            Route::post('/send-verification', [EmailVerificationController::class, 'sendCode'])->middleware('throttle:5,1');
            Route::post('/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:5,1');
            Route::get('/status', [EmailVerificationController::class, 'status']);
        });

        // Onboarding routes
        Route::prefix('onboarding')->group(function () {
            Route::get('/status', [OnboardingController::class, 'status']);
            Route::post('/name', [OnboardingController::class, 'name']);
            Route::post('/birthday', [OnboardingController::class, 'birthday']);
            Route::post('/represent', [OnboardingController::class, 'represent']);
        });

        // Routes that require verified email
        Route::middleware('verified')->group(function () {

            // Posts routes
            Route::prefix('posts')->group(function () {
                Route::get('/', [PostController::class, 'index']); // Feed
                Route::post('/', [PostController::class, 'store']); // Create
                Route::get('/{id}', [PostController::class, 'show']); // Get single
                Route::put('/{id}', [PostController::class, 'update']); // Update
                Route::delete('/{id}', [PostController::class, 'destroy']); // Delete
            });

            // Gamer-only routes
            Route::middleware('role:gamer')->prefix('gamer')->group(function () {
                Route::get('/', fn() => response()->json(['success' => true, 'message' => 'Gamer dashboard']));
            });

            // Recruiter-only routes
            Route::middleware('role:recruiter')->prefix('recruiter')->group(function () {
                Route::get('/', fn() => response()->json(['success' => true, 'message' => 'Recruiter dashboard']));
            });
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
