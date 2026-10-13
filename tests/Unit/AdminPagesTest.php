<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    public function test_empty_state_action_uses_the_shared_primary_button_style(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render(
            '<x-ui.empty-state title="No items" message="Create one to get started." action-label="Create item" action-route="'.route('dashboard').'" />'
        );

        $this->assertStringContainsString('button-base button-primary', $html);
        $this->assertStringContainsString('href="'.route('dashboard').'"', $html);
    }

    public function test_registered_admin_pages_render_without_database_setup(): void
    {
        foreach ([
            'special.error-403',
            'special.error-500',
            'special.error-503',
            'special.error-505',
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
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
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
            'chart.radar-showcase',
            'chart.radial-progress',
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
            'ui.carousel',
            'ui.ribbons',
            'layouts.sidebar-variants',
            'app.chat',
            'app.email',
            'app.support',
            'app.support.reply',
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
            'maps.vector',
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
            ->get(route('chart.examples'))
            ->assertOk()
            ->assertSee('x-init="mount($refs.chart)"', false)
            ->assertSee('Bar Chart Five')
            ->assertSee('Bar Chart Six')
            ->assertSee('Pie Chart Four')
            ->assertSee('Pie Chart Five');
        $this->actingAs($user)
            ->get(route('chart.radar-showcase'))
            ->assertOk()
            ->assertSee('Basic Radar')
            ->assertSee('Radar with Multiple Series')
            ->assertSee('Radar with Polygon Fill');
        $this->actingAs($user)
            ->get(route('chart.radial-progress'))
            ->assertOk()
            ->assertSee('Single Progress')
            ->assertSee('Multiple Progress')
            ->assertSee('Semi-circle Progress')
            ->assertSee('Progress with Labels');

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
            ->assertSee('Database Connection Timeout')
            ->assertSee(route('app.support.reply'), false)
            ->assertSee('selectDirection(option.id)', false)
            ->assertSee("localStorage.setItem('dir', this.direction)", false)
            ->assertSee('href="'.route('locale.switch', ['locale' => 'en']).'"', false)
            ->assertSee('href="'.route('locale.switch', ['locale' => 'fr']).'"', false);
        $this->actingAs($user)
            ->get(route('app.support.reply'))
            ->assertOk()
            ->assertSee('TKT-8838')
            ->assertSee('support-detail-reply', false)
            ->assertSee('inverter-status.png')
            ->assertSee('Resolve ticket');
        $this->actingAs($user)
            ->get(route('maps.vector'))
            ->assertOk()
            ->assertSee('id="mapVectorWorld"', false)
            ->assertSee('id="mapVectorRegions"', false);
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
            ->assertSee('focus-visible:ring-2', false)
            ->assertSee('tabindex="-1"', false)
            ->assertSee('id="component-date-selector"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('id="component-modal"', false)
            ->assertSee('aria-labelledby="component-modal-title"', false)
            ->assertSee('pageNumbers()', false)
            ->assertSee("'Home'", false)
            ->assertSee('element.tabIndex >= 0', false)
            ->assertSee('Carousel')
            ->assertSee('Ribbons');
        $this->actingAs($user)
            ->get(route('ui.carousel'))
            ->assertOk()
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('Previous slide');
        $this->actingAs($user)
            ->get(route('ui.ribbons'))
            ->assertOk()
            ->assertSee('Featured')
            ->assertSee('Needs attention');
        $this->actingAs($user)
            ->get(route('layouts.sidebar-variants'))
            ->assertOk()
            ->assertSee('Classic')
            ->assertSee('Sectioned')
            ->assertSee('Documentation')
            ->assertSee('Collapsible')
            ->assertSee('Nested')
            ->assertSee('Toggle');
        $this->actingAs($user)
            ->get(route('ai.settings'))
            ->assertOk()
            ->assertSee('API base URL')
            ->assertSee('Maximum output tokens')
            ->assertSee('Temperature')
            ->assertSee('not save, transmit, or connect');
        $this->actingAs($user)
            ->get(route('ui.alerts'))
            ->assertOk()
            ->assertSee('aria-live="assertive"', false)
            ->assertSee('role="alert"', false)
            ->assertSee('text-warning-500', false)
            ->assertDontSee('fill="#F04438"', false);

        $emptyState = \Illuminate\Support\Facades\Blade::render(
            '<x-ui.empty-state title="No items" message="Create one to get started." action-label="Create item" action-route="'.route('dashboard').'" />'
        );
        $this->assertStringContainsString('button-base', $emptyState);
        $this->assertStringContainsString('button-primary', $emptyState);
        $this->assertStringContainsString('href="'.route('dashboard').'"', $emptyState);
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

    public function test_dashboard_sidebar_renders_icons_and_a_bounded_scroll_region(): void
    {
        foreach (\App\Helpers\MenuHelper::getMenuGroups() as $group) {
            foreach ($group['items'] as $item) {
                $this->assertNotSame('', \App\Helpers\MenuHelper::getIconSvg($item['icon']));
            }
        }

        $dashboardItems = \App\Helpers\MenuHelper::getMainNavItems()[0]['subItems'];
        $dashboardIcons = array_map(
            fn (array $item) => \App\Helpers\MenuHelper::getIconSvg($item['icon']),
            $dashboardItems,
        );
        $this->assertCount(count($dashboardItems), array_unique($dashboardIcons));

        $user = new User([
            'name' => 'Dashboard reviewer',
            'email' => 'dashboard-reviewer@example.test',
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
        ]);
        $user->email_verified_at = now();

        $this->actingAs($user)
            ->get(route('dashboard.main'))
            ->assertOk()
            ->assertSee('overflow-y-auto overscroll-contain custom-scrollbar', false)
            ->assertSee('<span class="shrink-0 [&_svg]:h-4 [&_svg]:w-4"', false)
            ->assertSee('M3 6.5H14', false)
            ->assertSee('<svg width="24" height="24"', false);
    }

    public function test_routed_auth_pages_use_functional_branded_forms(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in | SolarShare Admin')
            ->assertSee('const savedDir = localStorage.getItem(\'dir\')', false)
            ->assertSee('id="signin-form"', false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('href="'.route('password.request').'"', false)
            ->assertSee(route('register'), false)
            ->assertSee('Good energy is better shared.')
            ->assertSee('lg:grid-cols-[1fr_0.95fr]', false)
            ->assertSee('Sign in with Google')
            ->assertSee('Sign in with Facebook')
            ->assertDontSee('Back to dashboard')
            ->assertSee('-translate-y-1/2', false);
        $this->get('/')
            ->assertOk()
            ->assertSee('goes further when we share it.');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create an account | SolarShare Admin')
            ->assertSee('id="register-form"', false)
            ->assertSee('action="'.route('register').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone_number"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="profile_photo"', false)
            ->assertSee('(optional)')
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Give good energy a second life.')
            ->assertSee('Sign up with Google')
            ->assertSee('Sign up with Facebook')
            ->assertDontSee('Back to dashboard')
            ->assertSee('lg:sticky lg:top-4 lg:block lg:self-start', false)
            ->assertSee('-translate-y-1/2', false);
        $this->assertTrue(is_subclass_of(\App\Models\User::class, \Illuminate\Contracts\Auth\MustVerifyEmail::class));

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('id="forgot-password-form"', false)
            ->assertSee('action="'.route('password.email').'"', false)
            ->assertSee('name="email"', false);

        $this->get(route('password.reset', ['token' => 'demo-token']))
            ->assertOk()
            ->assertSee('id="reset-password-form"', false)
            ->assertSee('action="'.route('password.store').'"', false)
            ->assertSee('name="token"', false)
            ->assertSee('name="password_confirmation"', false);
    }
}
