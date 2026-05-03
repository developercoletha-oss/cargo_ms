<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\ProfileController;
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
});

Route::get('/e-learning', fn () => redirect()->route('login'))->name('e-learning');
