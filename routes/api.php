<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\OwnerController;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the ApiRouteServiceProvider and all of them will
 | be assigned to the "api" middleware group. Make something great!
 |
 */

Route::middleware('auth:sanctum')->group(function () {
    // ---- Authenticated user -------------------------------------------------
    Route::get('/me', AccountController::class)->name('api.me');

    // ---- Buyer flows --------------------------------------------------------
    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/reservations', [BuyerController::class, 'reservations'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [BuyerController::class, 'showReservation'])->name('reservations.show');
        Route::get('/rentals', [BuyerController::class, 'rentals'])->name('rentals.index');
        Route::get('/rentals/{rental}', [BuyerController::class, 'showRental'])->name('rentals.show');
        Route::get('/payments', [BuyerController::class, 'payments'])->name('payments.index');
        Route::get('/notifications', [BuyerController::class, 'notifications'])->name('notifications.index');
        Route::patch('/notifications/{notification_id}', [BuyerController::class, 'readNotification'])->name('notifications.read');
        Route::delete('/notifications', [BuyerController::class, 'clearNotifications'])->name('notifications.clear');
    });

    // ---- Owner flows --------------------------------------------------------
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/reservations', [OwnerController::class, 'reservations'])->name('reservations.index');
        Route::patch('/reservations/{reservation}', [OwnerController::class, 'decideReservation'])->name('reservations.decide');
        Route::get('/rentals', [OwnerController::class, 'rentals'])->name('rentals.index');
        Route::get('/rentals/{rental}', [OwnerController::class, 'showRental'])->name('rentals.show');
        Route::get('/earnings', [OwnerController::class, 'earnings'])->name('earnings.index');
        Route::get('/notifications', [OwnerController::class, 'notifications'])->name('notifications.index');
        Route::patch('/notifications/{notification}', [OwnerController::class, 'readNotification'])->name('notifications.read');
    });

    // ---- Marketplace (read-only catalogue + booking) -----------------------
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('/', [MarketplaceController::class, 'index'])->name('index');
        Route::get('/{equipment}', [MarketplaceController::class, 'show'])->name('show');
        Route::get('/{equipment}/availability', [MarketplaceController::class, 'availability'])->name('availability');
    });

    Route::post('/reservations', [MarketplaceController::class, 'storeReservation'])->name('reservations.store');
    Route::post('/reservations/{reservation}/pay', [MarketplaceController::class, 'payReservation'])->name('reservations.pay');
});
