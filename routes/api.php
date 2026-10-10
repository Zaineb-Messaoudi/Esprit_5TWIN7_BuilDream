<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DocumentController;
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

// Stripe webhook (no auth required)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

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

    // ---- Documents -----------------------------------------------------------
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/templates', [DocumentController::class, 'templates'])->name('templates');
        Route::get('/templates/{template}', [DocumentController::class, 'showTemplate'])->name('templates.show');
        Route::get('/stats', [DocumentController::class, 'stats'])->name('stats');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::patch('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::post('/{document}/send', [DocumentController::class, 'sendForSignatures'])->name('send');
        Route::post('/{document}/share', [DocumentController::class, 'share'])->name('share');
        Route::get('/{document}/shares', [DocumentController::class, 'shares'])->name('shares');
        Route::delete('/{document}/shares/{share}', [DocumentController::class, 'revokeShare'])->name('shares.revoke');
        Route::get('/{document}/versions', [DocumentController::class, 'versions'])->name('versions');
        Route::post('/{document}/versions/{version}/restore', [DocumentController::class, 'restoreVersion'])->name('versions.restore');
        Route::get('/{document}/audit', [DocumentController::class, 'auditTrail'])->name('audit');

        Route::prefix('{document}/signatures')->name('signatures.')->group(function () {
            Route::post('/{signature}/sign', [DocumentController::class, 'sign'])->name('sign');
            Route::post('/{signature}/decline', [DocumentController::class, 'decline'])->name('decline');
        });
    });

    // ---- Analytics -----------------------------------------------------------
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
        Route::get('/bookings', [AnalyticsController::class, 'bookings'])->name('bookings');
        Route::get('/utilization', [AnalyticsController::class, 'utilization'])->name('utilization');
        Route::get('/user-growth', [AnalyticsController::class, 'userGrowth'])->name('user-growth');
        Route::get('/top-equipment', [AnalyticsController::class, 'topEquipment'])->name('top-equipment');
        Route::post('/record-event', [AnalyticsController::class, 'recordEvent'])->name('record-event');

        Route::prefix('dashboards')->name('dashboards.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'dashboards'])->name('index');
            Route::post('/', [AnalyticsController::class, 'storeDashboard'])->name('store');
            Route::get('/{dashboard}', [AnalyticsController::class, 'showDashboard'])->name('show');
            Route::patch('/{dashboard}', [AnalyticsController::class, 'updateDashboard'])->name('update');
            Route::delete('/{dashboard}', [AnalyticsController::class, 'destroyDashboard'])->name('destroy');

            Route::post('/{dashboard}/widgets', [AnalyticsController::class, 'addWidget'])->name('widgets.store');
            Route::patch('/widgets/{widget}', [AnalyticsController::class, 'updateWidget'])->name('widgets.update');
            Route::delete('/widgets/{widget}', [AnalyticsController::class, 'destroyWidget'])->name('widgets.destroy');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
            Route::post('/', [AnalyticsController::class, 'storeReport'])->name('store');
            Route::get('/{report}', [AnalyticsController::class, 'showReport'])->name('show');
            Route::post('/{report}/generate', [AnalyticsController::class, 'generateReport'])->name('generate');
            Route::get('/runs/{run}', [AnalyticsController::class, 'showReportRun'])->name('runs.show');
        });

        Route::prefix('models')->name('models.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'models'])->name('index');
            Route::get('/{model}/predictions', [AnalyticsController::class, 'modelPredictions'])->name('predictions');
            Route::post('/{model}/predict', [AnalyticsController::class, 'predict'])->name('predict');
        });
    });
});
