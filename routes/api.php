<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StripeConnectController;
use App\Http\Controllers\Api\StripeWebhookController;
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

    // ---- Reviews --------------------------------------------------------------
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/pending', [ReviewController::class, 'pending'])->name('pending');
        Route::get('/breakdown/{user}', [ReviewController::class, 'breakdown'])->name('breakdown');
        Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
        Route::post('/{rental}', [ReviewController::class, 'store'])->name('store');
        Route::post('/{review}/dispute', [ReviewController::class, 'dispute'])->name('dispute');
        Route::post('/{review}/respond', [ReviewController::class, 'respond'])->name('respond');

        // Admin routes
        Route::prefix('admin')->name('admin.')->middleware('can:admin-only')->group(function () {
            Route::get('/disputes', [ReviewController::class, 'disputes'])->name('disputes.index');
            Route::patch('/disputes/{dispute}', [ReviewController::class, 'resolveDispute'])->name('disputes.resolve');
        });
    });

    // ---- Deliveries -----------------------------------------------------------
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::get('/active', [DeliveryController::class, 'active'])->name('active');
        Route::get('/stats', [DeliveryController::class, 'stats'])->name('stats');
        Route::post('/{rental}', [DeliveryController::class, 'store'])->name('store');
        Route::get('/{delivery}', [DeliveryController::class, 'show'])->name('show');
        Route::patch('/{delivery}', [DeliveryController::class, 'updateStatus'])->name('update');
        Route::post('/{delivery}/schedule', [DeliveryController::class, 'schedule'])->name('schedule');

        // Admin routes
        Route::prefix('admin')->name('admin.')->middleware('can:admin-only')->group(function () {
            Route::get('/needing-attention', [DeliveryController::class, 'needingAttention'])->name('needing-attention');
            Route::post('/auto-cancel', [DeliveryController::class, 'autoCancel'])->name('auto-cancel');
        });
    });

    // ---- Chat ---------------------------------------------------------------
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/unread-count', [ChatController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{rental}/typing', [ChatController::class, 'typing'])->name('typing');

        Route::prefix('{rental}')->name('rental.')->group(function () {
            Route::get('/messages', [ChatController::class, 'index'])->name('messages.index');
            Route::post('/messages', [ChatController::class, 'store'])->name('messages.store');
            Route::post('/mark-read', [ChatController::class, 'markAsRead'])->name('messages.mark-read');
            Route::patch('/messages/{message}', [ChatController::class, 'update'])->name('messages.update');
            Route::delete('/messages/{message}', [ChatController::class, 'destroy'])->name('messages.destroy');
            Route::patch('/mute', [ChatController::class, 'mute'])->name('mute');
        });
    });

    // ---- Stripe Connect -------------------------------------------------------
    Route::prefix('stripe')->name('stripe.')->group(function () {
        Route::post('/onboard', [StripeConnectController::class, 'onboard'])->name('onboard');
        Route::get('/onboard/return', [StripeConnectController::class, 'return'])->name('onboard.return');
        Route::post('/onboard/refresh', [StripeConnectController::class, 'refresh'])->name('onboard.refresh');
        Route::get('/status', [StripeConnectController::class, 'status'])->name('status');
        Route::post('/escrow/{rental}', [StripeConnectController::class, 'createEscrow'])->name('escrow.create');
        Route::post('/escrow/{escrow}/confirm', [StripeConnectController::class, 'confirmEscrow'])->name('escrow.confirm');
        Route::post('/escrow/{escrow}/release', [StripeConnectController::class, 'release'])->name('escrow.release');
        Route::post('/escrow/{escrow}/refund', [StripeConnectController::class, 'refund'])->name('escrow.refund');
        Route::get('/escrow/{escrow}', [StripeConnectController::class, 'show'])->name('escrow.show');
    });
});

// Stripe webhook (no auth required)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');
