<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    public function test_registered_admin_pages_render_without_database_setup(): void
    {
        foreach ([
            'special.error-403',
            'special.error-500',
            'special.error-503',
            'special.access-denied',
            'special.maintenance',
            'special.coming-soon',
            'special.success',
            'special.under-construction',
            'special.session-expired',
            'special.auth-error',
            'special.logout-confirmation',
            'auth.lock-screen',
            'auth.two-factor',
        ] as $pageName) {
            $this->get(route($pageName))->assertOk();
        }

        $user = new User([
            'name' => 'Dashboard reviewer',
            'email' => 'dashboard-reviewer@example.test',
        ]);
        $user->email_verified_at = now();

        $pageNames = [
            'dashboard.main',
            'dashboard.ecommerce',
            'dashboard.analytics',
            'dashboard.marketing',
            'dashboard.crm',
            'dashboard.saas',
            'dashboard.ai',
            'ai.index',
            'ai.usage',
            'ai.text',
            'ai.image',
            'ai.video',
            'ai.code',
            'ai.chat',
            'ai.history',
            'ai.settings',
            'dashboard.stocks',
            'dashboard.finance',
            'dashboard.sales',
            'dashboard.logistics',
            'calendar',
            'app.tasks',
            'app.file-manager',
            'settings',
            'settings.notifications',
            'settings.security',
            'settings.preferences',
            'settings.sessions',
            'settings.connections',
            'profile.overview',
            'api-keys',
            'integrations',
            'form.elements',
            'form.advanced',
            'tables.basic',
            'tables.advanced',
            'pages.blank',
            'pages.error-404',
            'chart.line',
            'chart.bar',
            'chart.area',
            'chart.pie',
            'chart.donut',
            'chart.radial',
            'chart.examples',
            'ui.alerts',
            'ui.avatars',
            'ui.badge',
            'ui.buttons',
            'ui.image',
            'ui.videos',
            'ui.modals',
            'ui.skeletons',
            'ui.empty-state',
            'ui.components',
            'app.chat',
            'app.email',
            'app.support',
            'ecommerce.list',
            'ecommerce.detail',
            'ecommerce.cart',
            'ecommerce.checkout',
            'products.index',
            'products.create',
            'products.edit',
            'products.show',
            'categories.index',
            'orders.index',
            'orders.show',
            'customers.index',
            'customers.show',
            'invoices.index',
            'invoices.create',
            'invoices.show',
            'transactions.index',
            'transactions.show',
            'billing',
            'pricing',
            'ai.map',
            'maps.index',
            'faq.index',
            'layouts.full-width',
        ];

        foreach ($pageNames as $pageName) {
            $routeParameters = match ($pageName) {
                'products.show' => ['id' => 'sol-panel-420'],
                'products.edit' => ['id' => 'sol-panel-420'],
                'orders.show' => ['id' => 'SS-2084'],
                'customers.show' => ['id' => 'amira-ben-salem'],
                'invoices.show' => ['id' => 'INV-2026-084'],
                'transactions.show' => ['id' => 'TXN-48321'],
                default => [],
            };

            $this->actingAs($user)->get(route($pageName, $routeParameters))->assertStatus(200, "The {$pageName} page should render.");
        }

        $this->actingAs($user)
            ->get(route('calendar'))
            ->assertOk()
            ->assertSee('id="calendar"', false)
            ->assertSee('id="eventModal"', false)
            ->assertSee('id="event-start-date"', false)
            ->assertSee('btn-delete-event', false);
        $this->actingAs($user)
            ->get(route('app.chat'))
            ->assertOk()
            ->assertSee('id="chat-search"', false)
            ->assertSee('chat-attachment', false);
        $this->actingAs($user)
            ->get(route('app.email'))
            ->assertOk()
            ->assertSee('id="email-search"', false)
            ->assertSee('id="compose-to"', false)
            ->assertSee('forwardEmail', false)
            ->assertSee('email-attachment', false);
        $this->actingAs($user)
            ->get(route('app.file-manager'))
            ->assertOk()
            ->assertSee('id="file-type"', false)
            ->assertSee('storagePercent()', false)
            ->assertSee('URL.createObjectURL(file)', false)
            ->assertSee('Largest first');
        $this->actingAs($user)
            ->get(route('app.support'))
            ->assertOk()
            ->assertSee('id="ticket-search"', false)
            ->assertSee('id="ticket-status"', false)
            ->assertSee('Database Connection Timeout');
        $this->actingAs($user)
            ->get(route('app.tasks'))
            ->assertOk()
            ->assertSee('Kanban board')
            ->assertSee('id="edit-task-name"', false)
            ->assertSee('x-model="newTask.assignee"', false)
            ->assertSee('Move to next status')
            ->assertSee('Delete task')
            ->assertSee('type="date"', false);
        $this->actingAs($user)
            ->get(route('ui.components'))
            ->assertOk()
            ->assertSee('id="component-date-selector"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('id="component-modal"', false)
            ->assertSee('aria-labelledby="component-modal-title"', false)
            ->assertSee('pageNumbers()', false)
            ->assertSee("'Home'", false)
            ->assertSee('element.tabIndex >= 0', false);
        $this->actingAs($user)
            ->get(route('ui.alerts'))
            ->assertOk()
            ->assertSee('aria-live="assertive"', false)
            ->assertSee('role="alert"', false);
        $this->actingAs($user)
            ->get(route('settings.preferences'))
            ->assertOk()
            ->assertSee('solarshare-demo-preference-')
            ->assertSee('solarshare-demo-notification-');

        $this->actingAs($user)->get(route('products.show', ['id' => 'unknown-item']))->assertNotFound();
        $this->actingAs($user)->get(route('products.edit', ['id' => 'unknown-item']))->assertNotFound();
        $this->actingAs($user)->get(route('orders.show', ['id' => 'SS-9999']))->assertNotFound();
        $this->actingAs($user)->get(route('customers.show', ['id' => 'unknown-customer']))->assertNotFound();
        $this->actingAs($user)->get(route('invoices.show', ['id' => 'INV-2026-999']))->assertNotFound();
        $this->actingAs($user)->get(route('transactions.show', ['id' => 'TXN-99999']))->assertNotFound();
        $this->actingAs($user)->get(route('settings.password'))->assertRedirect(route('profile.edit').'#update-password');
        $this->actingAs($user)->get(route('ai.assistant'))->assertRedirect(route('ai.chat'));
        $this->actingAs($user)->get('/ai/unsupported-generator')->assertNotFound();
        $this->get('/a-route-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee(route('dashboard'), false);
        $this->getJson('/a-route-that-does-not-exist')
            ->assertNotFound()
            ->assertJsonStructure(['message', 'exception'])
            ->assertJsonPath('message', 'The route a-route-that-does-not-exist could not be found.');
    }

    public function test_sidebar_navigation_targets_registered_routes(): void
    {
        $menuGroups = \App\Helpers\MenuHelper::getMenuGroups();

        foreach ($menuGroups as $group) {
            foreach ($group['items'] as $item) {
                $links = $item['subItems'] ?? [$item];

                foreach ($links as $link) {
                    $path = parse_url($link['path'], PHP_URL_PATH) ?: '/';
                    $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($path, 'GET'));

                    $this->assertNotNull($route->getName(), "Sidebar link {$path} should have a named route.");
                }
            }
        }
    }

    public function test_routed_auth_pages_use_functional_branded_forms(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="signin-form"', false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('href="'.route('password.request').'"', false)
            ->assertSee(route('register'), false)
            ->assertSee('SolarShare workspace');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="register-form"', false)
            ->assertSee('action="'.route('register').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone_number"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('SolarShare workspace');
    }
}
