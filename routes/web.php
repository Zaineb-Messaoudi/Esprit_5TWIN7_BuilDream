<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\AiDemoController;
use App\Http\Controllers\AuthDemoController;
use App\Http\Controllers\ChartShowcaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpecialPageController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('dashboard.ecommerce');
})->middleware('auth')->name('dashboard');

foreach ([
    'error-403',
    'error-500',
    'error-503',
    'error-505',
    'access-denied',
    'maintenance',
    'coming-soon',
    'success',
    'under-construction',
    'session-expired',
    'auth-error',
    'logout-confirmation',
] as $specialPage) {
    Route::get('/'.$specialPage, [SpecialPageController::class, 'show'])
        ->defaults('page', $specialPage)
        ->name('special.'.$specialPage);
}
Route::get('/lock-screen', [AuthDemoController::class, 'show'])
    ->defaults('demo', 'lock-screen')
    ->name('auth.lock-screen');
Route::get('/two-factor', [AuthDemoController::class, 'show'])
    ->defaults('demo', 'two-factor')
    ->name('auth.two-factor');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard.main');
    Route::get('/stocks', [DashboardController::class, 'sector'])->defaults('dashboard', 'stocks')->name('dashboard.stocks');
    Route::get('/finance', [DashboardController::class, 'sector'])->defaults('dashboard', 'finance')->name('dashboard.finance');
    Route::get('/sales', [DashboardController::class, 'sector'])->defaults('dashboard', 'sales')->name('dashboard.sales');
    Route::get('/logistics', [DashboardController::class, 'sector'])->defaults('dashboard', 'logistics')->name('dashboard.logistics');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', function () {
        return view('pages.account.settings', ['title' => 'Account Settings', 'pageTitle' => 'General Settings', 'section' => 'general']);
    })->name('settings');
    Route::get('/settings/notifications', function () {
        return view('pages.account.settings', ['title' => 'Notification Settings', 'pageTitle' => 'Notifications', 'section' => 'notifications']);
    })->name('settings.notifications');
    Route::get('/settings/security', function () {
        return view('pages.account.settings', ['title' => 'Security Settings', 'pageTitle' => 'Security', 'section' => 'security']);
    })->name('settings.security');
    Route::get('/settings/preferences', function () {
        return view('pages.account.settings', ['title' => 'Preferences', 'pageTitle' => 'Preferences', 'section' => 'general']);
    })->name('settings.preferences');
    Route::get('/settings/password', function () {
        return redirect()->to(route('profile.edit').'#update-password');
    })->name('settings.password');
    Route::get('/settings/sessions', function () {
        return view('pages.account.settings', ['title' => 'Sessions', 'pageTitle' => 'Sessions and Security', 'section' => 'security']);
    })->name('settings.sessions');
    Route::get('/settings/connections', function () {
        return view('pages.account.settings', ['title' => 'Connected Accounts', 'pageTitle' => 'Connected Accounts', 'section' => 'integrations']);
    })->name('settings.connections');
    Route::get('/profile/overview', function () {
        return view('pages.account.profile-overview', ['title' => 'Profile Overview']);
    })->name('profile.overview');
    Route::get('/api-keys', function () {
        return view('pages.account.settings', ['title' => 'API Keys', 'pageTitle' => 'API Keys', 'section' => 'api-keys']);
    })->name('api-keys');
    Route::get('/integrations', function () {
        return view('pages.account.settings', ['title' => 'Integrations', 'pageTitle' => 'Integrations', 'section' => 'integrations']);
    })->name('integrations');

    // Admin User Management
    Route::middleware('can:admin-only')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::resource('rentals', RentalController::class);
    });


    // Dashboard Routes
    Route::get('/ecommerce', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
    })->name('dashboard.ecommerce');
    Route::get('/analytics', function () {
        return view('pages.dashboard.analytics', ['title' => 'Analytics Dashboard']);
    })->name('dashboard.analytics');
    Route::get('/marketing', function () {
        return view('pages.dashboard.marketing', ['title' => 'Marketing Dashboard']);
    })->name('dashboard.marketing');
    Route::get('/crm', function () {
        return view('pages.dashboard.crm', ['title' => 'CRM Dashboard']);
    })->name('dashboard.crm');
    Route::get('/saas', function () {
        return view('pages.dashboard.saas', ['title' => 'SaaS Dashboard']);
    })->name('dashboard.saas');
    Route::get('/ai-dashboard', function () {
        return view('pages.dashboard.ai', ['title' => 'AI Dashboard']);
    })->name('dashboard.ai');
    Route::get('/ai', [AiDemoController::class, 'index'])->name('ai.index');
    Route::get('/ai/usage', [AiDemoController::class, 'usage'])->name('ai.usage');
    Route::get('/ai/text-generator', [AiDemoController::class, 'tool'])->defaults('tool', 'text')->name('ai.text');
    Route::get('/ai/image-generator', [AiDemoController::class, 'tool'])->defaults('tool', 'image')->name('ai.image');
    Route::get('/ai/video-generator', [AiDemoController::class, 'tool'])->defaults('tool', 'video')->name('ai.video');
    Route::get('/ai/code-generator', [AiDemoController::class, 'tool'])->defaults('tool', 'code')->name('ai.code');
    Route::get('/ai/chat', [AiDemoController::class, 'tool'])->defaults('tool', 'chat')->name('ai.chat');
    Route::get('/ai/history', [AiDemoController::class, 'tool'])->defaults('tool', 'history')->name('ai.history');
    Route::get('/ai/settings', [AiDemoController::class, 'tool'])->defaults('tool', 'settings')->name('ai.settings');

    // Calendar
    Route::get('/calendar', function () {
        return view('pages.applications.calendar', ['title' => 'Calendar']);
    })->name('calendar');
    Route::get('/tasks', function () {
        return view('pages.applications.tasks', ['title' => 'Tasks']);
    })->name('app.tasks');
    Route::get('/file-manager', function () {
        return view('pages.applications.file-manager', ['title' => 'File Manager']);
    })->name('app.file-manager');

    // Forms
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form.elements');
    Route::get('/advanced-forms', function () {
        return view('pages.form.advanced-elements', ['title' => 'Advanced Form Elements']);
    })->name('form.advanced');

    // Tables
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('tables.basic');
    Route::get('/advanced-tables', function () {
        return view('pages.tables.advanced', ['title' => 'Advanced Tables']);
    })->name('tables.advanced');

    // Pages
    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank Page']);
    })->name('pages.blank');
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => '404 Not Found']);
    })->name('pages.error-404');

    // Charts
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('chart.line');
    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('chart.bar');
    Route::get('/area-chart', function () {
        return view('pages.chart.showcase', ['title' => 'Area Chart', 'type' => 'area']);
    })->name('chart.area');
    Route::get('/pie-chart', function () {
        return view('pages.chart.showcase', ['title' => 'Pie Chart', 'type' => 'pie']);
    })->name('chart.pie');
    Route::get('/donut-chart', function () {
        return view('pages.chart.showcase', ['title' => 'Donut Chart', 'type' => 'donut']);
    })->name('chart.donut');
    Route::get('/radial-chart', function () {
        return view('pages.chart.showcase', ['title' => 'Radial Chart', 'type' => 'radial']);
    })->name('chart.radial');
    Route::get('/chart-examples', [ChartShowcaseController::class, 'index'])->name('chart.examples');
    Route::get('/radar-chart', [ChartShowcaseController::class, 'radar'])->name('chart.radar-showcase');
    Route::get('/radial-progress-charts', [ChartShowcaseController::class, 'radialProgress'])->name('chart.radial-progress');

    // UI Elements
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('ui.alerts');
    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('ui.avatars');
    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('ui.badge');
    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('ui.buttons');
    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('ui.image');
    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('ui.videos');
    Route::get('/modals', function () {
        return view('pages.ui-elements.states', ['title' => 'Modals', 'type' => 'modals']);
    })->name('ui.modals');
    Route::get('/skeletons', function () {
        return view('pages.ui-elements.states', ['title' => 'Skeletons', 'type' => 'skeletons']);
    })->name('ui.skeletons');
    Route::get('/empty-state', function () {
        return view('pages.ui-elements.states', ['title' => 'Empty States', 'type' => 'empty-state']);
    })->name('ui.empty-state');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    Route::get('/layouts/full-width', function () {
        return view('pages.layouts.full-width-demo', ['title' => __('Full-width layout example')]);
    })->name('layouts.full-width');
    Route::get('/components', function () {
        return view('pages.ui-elements.components', ['title' => 'Component Library']);
    })->name('ui.components');
    Route::get('/carousel', function () {
        return view('pages.ui-elements.carousel', ['title' => 'Carousel']);
    })->name('ui.carousel');
    Route::get('/ribbons', function () {
        return view('pages.ui-elements.ribbons', ['title' => 'Ribbons']);
    })->name('ui.ribbons');
    Route::get('/layouts/sidebar-variants', function () {
        return view('pages.layouts.sidebar-variants', ['title' => 'Sidebar Variants']);
    })->name('layouts.sidebar-variants');

    // Applications
    Route::get('/chat', function () {
        return view('pages.applications.chat', ['title' => 'Chat App']);
    })->name('app.chat');
    Route::get('/email', function () {
        return view('pages.applications.email', ['title' => 'Email App']);
    })->name('app.email');
    Route::get('/support-ticket', function () {
        return view('pages.applications.support', ['title' => 'Support Tickets']);
    })->name('app.support');
    Route::get('/support-ticket-reply', function () {
        return view('pages.applications.support-ticket-detail', ['title' => 'Ticket Reply']);
    })->name('app.support.reply');

    // Ecommerce
    Route::get('/product-list', function () {
        return view('pages.ecommerce.operations', ['title' => 'Product Inventory', 'type' => 'list']);
    })->name('ecommerce.list');
    Route::get('/product-detail', function () {
        return view('pages.ecommerce.operations', ['title' => 'Product Details', 'type' => 'detail']);
    })->name('ecommerce.detail');
    Route::get('/cart', function () {
        return view('pages.ecommerce.operations', ['title' => 'Shopping Cart', 'type' => 'cart']);
    })->name('ecommerce.cart');
    Route::get('/checkout', function () {
        return view('pages.ecommerce.operations', ['title' => 'Checkout', 'type' => 'checkout']);
    })->name('ecommerce.checkout');
    Route::get('/products', [EcommerceController::class, 'index'])->defaults('resource', 'products')->name('products.index');
    Route::get('/products/create', [EcommerceController::class, 'create'])->defaults('resource', 'products')->name('products.create');
    Route::get('/products/{id}/edit', [EcommerceController::class, 'edit'])->defaults('resource', 'products')->where('id', '[a-z0-9-]+')->name('products.edit');
    Route::get('/products/{id}', [EcommerceController::class, 'show'])->defaults('resource', 'products')->where('id', '[a-z0-9-]+')->name('products.show');
    Route::get('/categories', [EcommerceController::class, 'index'])->defaults('resource', 'categories')->name('categories.index');
    Route::get('/orders', [EcommerceController::class, 'index'])->defaults('resource', 'orders')->name('orders.index');
    Route::get('/orders/{id}', [EcommerceController::class, 'show'])->defaults('resource', 'orders')->where('id', 'SS-[0-9]+')->name('orders.show');
    Route::get('/customers', [EcommerceController::class, 'index'])->defaults('resource', 'customers')->name('customers.index');
    Route::get('/customers/{id}', [EcommerceController::class, 'show'])->defaults('resource', 'customers')->where('id', '[a-z-]+')->name('customers.show');
    Route::get('/invoices', [EcommerceController::class, 'index'])->defaults('resource', 'invoices')->name('invoices.index');
    Route::get('/invoices/create', [EcommerceController::class, 'create'])->defaults('resource', 'invoices')->name('invoices.create');
    Route::get('/invoices/{id}', [EcommerceController::class, 'show'])->defaults('resource', 'invoices')->where('id', 'INV-[A-Z0-9-]+')->name('invoices.show');
    Route::get('/transactions', [EcommerceController::class, 'index'])->defaults('resource', 'transactions')->name('transactions.index');
    Route::get('/transactions/{id}', [EcommerceController::class, 'show'])->defaults('resource', 'transactions')->where('id', 'TXN-[0-9]+')->name('transactions.show');
    Route::get('/billing', [EcommerceController::class, 'billing'])->name('billing');
    Route::get('/pricing', [EcommerceController::class, 'pricing'])->name('pricing');

    // AI & Maps
    Route::get('/ai-assistant', function () {
        return redirect()->route('ai.chat');
    })->name('ai.assistant');
    Route::get('/map-view', function () {
        return view('pages.applications.map', ['title' => 'Regional Service Coverage']);
    })->name('ai.map');
    Route::get('/maps', function () {
        return view('pages.applications.maps', ['title' => __('Map examples')]);
    })->name('maps.index');
    Route::get('/vector-maps', function () {
        return view('pages.applications.vector-maps', ['title' => __('Vector Maps')]);
    })->name('maps.vector');
});

require __DIR__.'/auth.php';
