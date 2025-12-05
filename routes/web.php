<?php

use App\Http\Controllers\Admin\AuthController;
use App\Livewire\Admin\AdminList;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PermissionList;
use App\Livewire\Admin\UserList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // Guest routes (not authenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    });

    // Authenticated admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Dashboard
        Route::middleware('admin.permission:dashboard')->group(function () {
            Route::get('/', Dashboard::class)->name('admin.dashboard');
        });

        // Admin management
        Route::middleware('admin.permission:admins')->group(function () {
            Route::get('/admins', AdminList::class)->name('admin.admins');
        });

        // User management
        Route::middleware('admin.permission:users')->group(function () {
            Route::get('/users', UserList::class)->name('admin.users');
        });

        // Permission management
        Route::middleware('admin.permission:permissions')->group(function () {
            Route::get('/permissions', PermissionList::class)->name('admin.permissions');
        });
    });
});


