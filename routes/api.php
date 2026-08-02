<?php

use App\Http\Controllers\Api\V1\TrackController;
use App\Http\Controllers\Api\V1\TransporterGpsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Objective #4: Customer Live Transit Progress Tracking
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Backend Specifications (API & Logic)
    Route::get('/track/{trackingNumber?}', [TrackController::class, 'track'])->name('api.v1.track');
    Route::get('/customer/active-cargoes', [TrackController::class, 'activeCargoes'])->name('api.v1.customer.active-cargoes');

    // 2. Secondary Transporter Update Endpoint
    Route::post('/shipment/update-checkpoint', [TrackController::class, 'updateCheckpoint'])->name('api.v1.shipment.update-checkpoint');
    Route::post('/transporter/update-gps', [TransporterGpsController::class, 'updateGps'])->name('api.v1.transporter.update-gps');
});
