<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DormitoryApplicationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AllocationController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/archived', [UserController::class, 'archived'])->name('users.archived');
        Route::patch('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
        Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
    });

    // Any authenticated user can edit their own profile
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

    // Dormitory Applications (student can create; admin/dorm_manager can review)
    Route::middleware('role:student')->group(function () {
        Route::get('/applications/create', [DormitoryApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [DormitoryApplicationController::class, 'store'])->name('applications.store');
    });

    Route::get('/applications', [DormitoryApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [DormitoryApplicationController::class, 'show'])->name('applications.show');

    Route::middleware('role:admin,dorm_manager')->group(function () {
        Route::patch('/applications/{id}/status', [DormitoryApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    });

    // Room Management (admin + dorm_manager)
    Route::middleware('role:admin,dorm_manager')->group(function () {
        Route::resource('rooms', RoomController::class);
    });

    // Room Allocation (admin + dorm_manager)
    Route::middleware('role:admin,dorm_manager')->group(function () {
        Route::get('/allocations', [AllocationController::class, 'index'])->name('allocations.index');
        Route::get('/allocations/create', [AllocationController::class, 'create'])->name('allocations.create');
        Route::post('/allocations', [AllocationController::class, 'store'])->name('allocations.store');
        Route::patch('/allocations/{id}/status', [AllocationController::class, 'updateStatus'])->name('allocations.updateStatus');
        Route::delete('/allocations/{id}', [AllocationController::class, 'destroy'])->name('allocations.destroy');
    });
});
