<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\PlayStationController;
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
        Route::put('/user', [AuthController::class, 'update']);
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

            // Games routes
            Route::prefix('games')->group(function () {
                Route::get('/', [GameController::class, 'index']); // List all games
            });

            // User's favorite games routes
            Route::prefix('user/favorite-games')->group(function () {
                Route::get('/', [GameController::class, 'getFavorites']); // Get favorites
                Route::post('/', [GameController::class, 'addFavorites']); // Add to favorites
                Route::put('/', [GameController::class, 'setFavorites']); // Replace all favorites
                Route::delete('/{gameId}', [GameController::class, 'removeFavorite']); // Remove from favorites
            });

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

            // PlayStation Integration routes
            Route::prefix('playstation')->group(function () {
                Route::get('/status', [PlayStationController::class, 'status']);
                Route::post('/link', [PlayStationController::class, 'link']);
                Route::post('/sync', [PlayStationController::class, 'sync']);
                Route::get('/games', [PlayStationController::class, 'games']);
                Route::post('/games/manual', [PlayStationController::class, 'addGameManually']);
                Route::delete('/disconnect', [PlayStationController::class, 'disconnect']);
            });

            // Leaderboard routes
            Route::prefix('leaderboard')->group(function () {
                Route::get('/all-time', [LeaderboardController::class, 'allTime']);
                Route::get('/monthly', [LeaderboardController::class, 'monthly']);
                Route::get('/weekly', [LeaderboardController::class, 'weekly']);
            });
        });

    });
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
