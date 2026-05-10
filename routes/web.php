<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\CargoController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\ShipmentController;
use App\Http\Controllers\Dashboard\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
require __DIR__.'/auth.php';
Route::view('/home', 'home');
Route::view('/about', 'about')->name('about');

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown-data', [NotificationController::class, 'dropdownData'])->name('notifications.dropdown-data');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/{notificationId}', [NotificationController::class, 'show'])->name('notifications.show');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Shipments (role-based access)
    Route::middleware('role:admin,hgadmin,manager,staff')->group(function () {
        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    });

    // Cargo workflow
    Route::middleware('role:customer,admin,hgadmin,manager,staff')->group(function () {
        Route::get('/cargo', [CargoController::class, 'index'])->name('cargo.index');
        Route::post('/cargo', [CargoController::class, 'store'])->name('cargo.store');
        Route::put('/cargo/{cargo}', [CargoController::class, 'update'])->name('cargo.update');
        Route::delete('/cargo/{cargo}', [CargoController::class, 'destroy'])->name('cargo.destroy');
        Route::post('/cargo/{cargo}/approve', [CargoController::class, 'approve'])->name('cargo.approve');
        Route::post('/cargo/{cargo}/disapprove', [CargoController::class, 'disapprove'])->name('cargo.disapprove');
        Route::post('/cargo/{cargo}/assign', [CargoController::class, 'assign'])->name('cargo.assign');
    });

    // User Management (admin + hgadmin)
    Route::middleware('role:admin,hgadmin')->group(function () {
        Route::post('users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
        Route::resource('users', UserManagementController::class)->names('users');
    });
});

Route::get('/e-learning', fn () => redirect()->route('login'))->name('e-learning');
