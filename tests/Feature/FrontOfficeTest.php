<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontOfficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_office_home_and_catalog_use_solarshare_visual_assets(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Clean energy')
            ->assertSee('goes further when we share it.')
            ->assertSee('images/front/battery-station.svg')
            ->assertDontSee('id="hero-q"', false)
            ->assertSee('Borrow. Use. Return.')
            ->assertSee('Rent the energy. Skip the extra stuff.')
            ->assertSee('Illustration · SolarShare concept')
            ->assertSee('A shared-energy marketplace for everyone')
            ->assertSee('SolarShare brings renters, equipment owners and local communities together')
            ->assertSee('Share your equipment')
            ->assertSee('max-w-7xl')
            ->assertSee('px-6')
            ->assertSee('id="home-search"', false)
            ->assertDontSee('animate-energy-float')
            ->assertDontSee('0</dd>')
            ->assertDontSee('Borrow for a weekend. Share for a better future.')
            ->assertDontSee('Have gear to share?')
            ->assertSee('Listings shown here are illustrative.')
            ->assertSee('One platform. More ways to share clean energy.')
            ->assertSee('For renters: find the right power')
            ->assertSee('For owners: put good gear to work')
            ->assertSee('For everyone: keep gear in use')
            ->assertDontSee('Neighbours already sharing')
            ->assertSee('<svg', false)
            ->assertSee('id="how-it-works"', false)
            ->assertSee(route('front.plans'), false)
            ->assertSee(route('front.catalog'), false);

        $this->get(route('front.catalog'))
            ->assertOk()
            ->assertSee('Power for the plan you already have.')
            ->assertSee('Available')
            ->assertSee('images/front/solar-panel.svg')
            ->assertSee('images/front/battery-station.svg')
            ->assertSee('images/front/wind-turbine.svg')
            ->assertSee(route('front.equipment.show', 1), false);
    }

    public function test_solarshare_branding_is_shared_by_front_office_back_office_and_auth_screens(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('images/brand/solarshare-icon.png')
            ->assertSee('SolarShare');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('images/brand/solarshare-icon.png')
            ->assertSee('A community-powered energy future.')
            ->assertSee('images/brand/solarshare-icon.png');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('images/brand/solarshare-icon.png')
            ->assertSee('images/brand/solarshare-icon.png');

        $admin = new User([
            'name' => 'SolarShare Admin',
            'email' => 'admin@example.test',
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
        ]);
        $admin->email_verified_at = now();

        $this->actingAs($admin)
            ->get(route('dashboard.ecommerce'))
            ->assertOk()
            ->assertSee('images/brand/solarshare-icon.png')
            ->assertSee('SolarShare');
    }

    public function test_configured_front_office_pages_are_reachable(): void
    {
        foreach (config('front.pages') as $slug => $page) {
            if (in_array($slug, ['my-equipment-detail', 'my-equipment-edit', 'reserve', 'booking-summary', 'payment', 'confirmed', 'invoice'], true)) {
                continue;
            }
            $routeName = 'front.'.$slug;

            $response = $this->get(route($routeName));

            if (($page['shell'] ?? null) === 'account') {
                $response->assertRedirect(route('login'));

                continue;
            }

            $response->assertOk()->assertSee('SolarShare');
        }
    }

    public function test_front_office_templates_include_keyboard_navigation_and_accessible_catalog_controls(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Skip to main content')
            ->assertSee('id="main-content" tabindex="-1"', false)
            ->assertSee('aria-label="Main navigation"', false)
            ->assertSee('Toggle menu')
            ->assertSee('front-navigation-toggle-mobile')
            ->assertSee('Sign in')
            ->assertSee('Create account')
            ->assertDontSee('Sign out');

        $this->get(route('front.catalog'))
            ->assertOk()
            ->assertSee('for="q"', false)
            ->assertSee('for="category"', false)
            ->assertSee('role="status"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_front_office_sidebar_is_persistent_and_outside_the_backdrop_filtered_header(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $headerEnd = strpos($html, '</header>');
        $sidebarStart = strpos($html, '<aside id="front-sidebar"');
        $layoutToggle = strpos($html, 'id="front-navigation-toggle"');
        $brandLogo = strpos($html, 'aria-label="SolarShare"');

        $this->assertNotFalse($headerEnd);
        $this->assertNotFalse($sidebarStart);
        $this->assertLessThan($sidebarStart, $headerEnd);
        $this->assertNotFalse($layoutToggle);
        $this->assertNotFalse($brandLogo);
        $this->assertLessThan($brandLogo, $layoutToggle);
        $toggleId = strpos($html, 'id="front-navigation-toggle"');
        $toggleStart = strrpos(substr($html, 0, $toggleId), '<button');
        $toggleEnd = strpos($html, '</button>', $toggleStart);
        $layoutToggle = substr($html, $toggleStart, $toggleEnd - $toggleStart);
        $this->assertStringContainsString('aria-label="Use sidebar navigation"', $layoutToggle);
        $this->assertStringContainsString('<svg', $layoutToggle);
        $this->assertStringNotContainsString('Side menu', $layoutToggle);
        $this->assertStringNotContainsString('xl:hidden', $layoutToggle);
        $this->assertStringNotContainsString(':title=', $layoutToggle);
        $this->assertStringContainsString("localStorage.setItem('navigation-layout', this.sidebarMode ? 'sidebar' : 'top')", $html);
        $this->assertStringContainsString('role="complementary"', $html);
        $this->assertStringNotContainsString('front-sidebar-backdrop', $html);
        $this->assertStringNotContainsString('closeSidebar()', $html);

        $styles = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('html.nav-sidebar .front-sidebar-panel', $styles);
        $this->assertStringContainsString('inset-block-start: calc(4.5rem + 0.75rem)', $styles);
        $this->assertStringContainsString('html.nav-sidebar .front-content-shell', $styles);
    }

    public function test_workspace_sidebar_shows_named_links_without_a_wrapped_workspace_heading(): void
    {
        $owner = new User([
            'name' => 'Sami Owner',
            'email' => 'owner@example.test',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
        ]);
        $owner->email_verified_at = now();

        $html = $this->actingAs($owner)->get(route('front.owner-dashboard'))->assertOk()->getContent();
        $sidebar = substr($html, strpos($html, '<aside id="front-sidebar"'), strpos($html, '</aside>') - strpos($html, '<aside id="front-sidebar"'));

        $this->assertStringContainsString('class="sr-only">OWNER STUDIO</h2>', $sidebar);
        $this->assertStringNotContainsString('front-sidebar-section-title', $sidebar);
        $this->assertStringContainsString('aria-label="Maintenance"', $sidebar);
        $this->assertStringContainsString('<span>Maintenance</span>', $sidebar);
        $this->assertStringContainsString('<span>Reservations</span>', $sidebar);
        $this->assertStringNotContainsString('SolarShare home', $sidebar);
        $this->assertStringNotContainsString('Use top navigation', $sidebar);
        $this->assertStringNotContainsString('Sign out', $sidebar);

        $buyer = new User([
            'name' => 'Leila Buyer',
            'email' => 'buyer@example.test',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $buyer->email_verified_at = now();

        $buyerHtml = $this->actingAs($buyer)->get(route('front.my-dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('My SolarShare', $buyerHtml);
        $this->assertStringContainsString('aria-label="Payments &amp; invoices"', $buyerHtml);
        $this->assertStringContainsString('<span>Payments &amp; invoices</span>', $buyerHtml);
    }

    public function test_language_can_switch_between_english_and_french_across_pages(): void
    {
        $this->from(route('front.catalog'))
            ->get(route('locale.switch', ['locale' => 'fr']))
            ->assertRedirect(route('front.catalog'));

        $this->get(route('front.catalog'))
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('Accueil')
            ->assertSee('Catalogue')
            ->assertSee('lang="fr"', false)
            ->assertSee('aria-label="Langue"', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('Passer en anglais')
            ->assertSee('Passer en français');

        $owner = new User([
            'name' => 'Sami Owner',
            'email' => 'owner@example.test',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
        ]);
        $owner->email_verified_at = now();

        $this->actingAs($owner)
            ->get(route('front.owner-dashboard'))
            ->assertOk()
            ->assertSee('ESPACE PROPRIÉTAIRE')
            ->assertSee('Revenus par mois')
            ->assertSee('Accès rapide');

        $this->get(route('locale.switch', ['locale' => 'en']))
            ->assertRedirect();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('Catalog');
    }

    public function test_signup_defers_renter_or_owner_choice_until_after_account_creation(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="role"', false)
            ->assertDontSee('How will you use SolarShare?');

        $this->get(route('role.setup'))
            ->assertRedirect(route('login'));
    }

    public function test_owner_and_buyer_receive_separate_workspace_dashboards(): void
    {
        $owner = new User([
            'name' => 'Sami Owner',
            'email' => 'owner@example.test',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
        ]);
        $owner->email_verified_at = now();

        $this->actingAs($owner)
            ->get(route('front.owner-dashboard'))
            ->assertOk()
            ->assertSee('Owner workspace')
            ->assertSee('Your performance at a glance')
            ->assertSee('Quick access')
            ->assertSee(route('front.my-publish'), false);
        $ownerDashboard = $this->get(route('front.owner-dashboard'));
        $ownerDashboard
            ->assertSee(route('home'), false)
            ->assertSee(route('front.catalog'), false)
            ->assertSee('Home')
            ->assertSee('Catalog')
            ->assertSee('Owner workspace')
            ->assertSee('Reservations')
            ->assertSee('Rentals')
            ->assertSee('Contract')
            ->assertSee('Extensions')
            ->assertSee('Notifications')
            ->assertSee('Profile')
            ->assertSee('Equipment')
            ->assertSee('Inspections')
            ->assertSee('Quick access')
            ->assertSee('Availability')
            ->assertSee('Earnings')
            ->assertSee('Illustrative dashboard data.')
            ->assertSee('Revenue per month')
            ->assertSee('Reservations by status')
            ->assertSee('Equipment status')
            ->assertSee('Rentals by equipment')
            ->assertSee('Total equipment')
            ->assertSee('Available now')
            ->assertSee('Active rentals')
            ->assertSee('Pending reservations')
            ->assertSee('Extension requests')
            ->assertSee('Revenue this month')
            ->assertSee('Returns due this week')
            ->assertSee('In maintenance')
            ->assertSee('To do now')
            ->assertSee('Inspections')
            ->assertSee('Latest activity')
            ->assertSee('1,240 TND');
        preg_match('/<nav[^>]*aria-label="Main navigation"[^>]*>(.*?)<\\/nav>/s', (string) $ownerDashboard->getContent(), $navigation);
        expect($navigation[1] ?? '')->not->toContain('How it works')->not->toContain('For owners');
        $this->assertStringContainsString('2xl:flex', (string) $ownerDashboard->getContent());
        $this->assertStringNotContainsString('overflow-x-auto rounded-full', (string) $ownerDashboard->getContent());
        preg_match('/<nav[^>]*aria-label="Owner workspace navigation"[^>]*>(.*?)<\\/nav>/s', (string) $ownerDashboard->getContent(), $ownerNavigation);
        expect($ownerNavigation[1] ?? '')->not->toContain('overflow-x-auto');
        $this->get(route('front.my-reservations'))
            ->assertOk()
            ->assertDontSee('aria-label="My space"', false)
            ->assertDontSee('lg:grid-cols-[15rem_1fr]', false)
            ->assertSee('aria-label="Main navigation"', false);
        $this->get(route('front.my-dashboard'))->assertForbidden();
        $this->get(route('front.my-equipment'))->assertOk();

        $buyer = new User([
            'name' => 'Leila Buyer',
            'email' => 'buyer@example.test',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $buyer->email_verified_at = now();

        $this->actingAs($buyer)
            ->get(route('front.my-dashboard'))
            ->assertOk()
            ->assertSee('Your SolarShare')
            ->assertSee(route('front.my-reservations'), false)
            ->assertSee('Buyer dashboard statistics')
            ->assertSee('Active rentals')
            ->assertSee('Upcoming reservations')
            ->assertSee('Pending reservations')
            ->assertSee('Total spent')
            ->assertSee('Total rental days')
            ->assertSee('Extensions awaiting owner')
            ->assertSee('Spending per month')
            ->assertSee('Rentals by category')
            ->assertSee('Reservations by status')
            ->assertSee('Up next · RES-1042')
            ->assertSee('To do now');
        $this->get(route('front.my-equipment'))->assertForbidden();
    }

    public function test_buyer_workspace_screens_render_as_frontend_previews(): void
    {
        $buyer = new User([
            'name' => 'Leila Buyer',
            'email' => 'buyer-workspace@example.test',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $buyer->email_verified_at = now();
        $this->actingAs($buyer);

        foreach ([
            ['front.my-reservations', 'Your reservations'],
            ['front.buyer-reservation-detail', 'Booking journey'],
            ['front.my-rentals', 'Rental days per month'],
            ['front.buyer-rental-detail', 'Contract CTR-2031-01'],
            ['front.my-contract', 'Terms of use'],
            ['front.my-extensions', 'Request more time'],
            ['front.my-payments', 'Payments & invoices'],
            ['front.my-notifications', 'Your updates'],
        ] as [$route, $content]) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($content)
                ->assertSee('Frontend preview with sample data.')
                ->assertSee('Buyer account navigation');
        }

        $this->get(route('front.my-rentals'))
            ->assertSee('Extensions')
            ->assertSee('Payments & invoices');
        $this->get(route('front.my-reservations'))
            ->assertSee('Cancel this pending reservation?')
            ->assertSee('Confirm cancellation');
        $this->get(route('front.my-payments'))
            ->assertSee('Spending per month')
            ->assertSee('Payments by method')
            ->assertSee('INV-2026-0142');
        $this->get(route('front.my-dashboard'))
            ->assertSee('aria-label="Buyer account navigation"', false)
            ->assertSee('rounded-2xl border border-gray-200 bg-white/95', false);
    }

    public function test_owner_workspace_pages_render_as_frontend_previews(): void
    {
        $owner = User::factory()->create([
            'name' => 'Sami Owner',
            'email' => 'owner@example.test',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
        ]);
        $this->actingAs($owner);

        $ownerEquipment = Equipment::factory()->create(['owner_id' => $owner->id]);

        foreach ([
            ['front.my-equipment', 'Your listings'],
            ['front.my-publish', 'Energy profile'],
            ['front.my-equipment-detail', 'Listing details'],
            ['front.my-equipment-edit', 'Save changes'],
            ['front.my-calendar', 'October 2026'],
            ['front.my-reservations', 'Reservations on your equipment'],
            ['front.my-reservation-detail', 'Payment & invoice'],
            ['front.my-rentals', 'Rental history'],
            ['front.my-rental-detail', 'Rental RNT-2031'],
            ['front.my-contract', 'Print contract'],
            ['front.my-extensions', 'Extension requests over time'],
            ['front.my-earnings', 'Payments & invoices'],
            ['front.my-maintenance', 'Maintenance cost by equipment'],
            ['front.my-inspections', 'Inspection log'],
            ['front.my-notifications', 'Notification preferences'],
        ] as [$route, $content]) {
            $parameters = in_array($route, ['front.my-equipment-detail', 'front.my-equipment-edit'], true)
                ? [$ownerEquipment]
                : [];

            $response = $this->get(route($route, $parameters));

            $response
                ->assertOk()
                ->assertSee($content);

            if (! in_array($route, ['front.my-equipment', 'front.my-publish', 'front.my-equipment-detail', 'front.my-equipment-edit'], true)) {
                $response->assertSee('Interactive frontend preview.');
            }
        }

        $this->get(route('front.my-earnings'))
            ->assertSee('Revenue by category')
            ->assertSee('Payments by method')
            ->assertSee('Revenue vs maintenance cost');
        $this->get(route('front.my-calendar'))->assertSee('SERVICE');
        $this->get(route('front.my-maintenance'))->assertSee('Maintenance cost by equipment');
        $this->get(route('front.my-extensions'))->assertSee('Extension requests over time');
    }

    public function test_equipment_detail_and_demo_booking_pages_render_without_claiming_live_booking(): void
    {
        $this->get(route('front.equipment.show', 1))
            ->assertOk()
            ->assertSee('Energy profile')
            ->assertSee('Portable battery 1000 Wh')
            ->assertSee('Available')
            ->assertSee('Availability preview')
            ->assertSee('Browse-only catalogue')
            ->assertDontSee('Preview booking flow');

        $this->get(route('front.reserve'))
            ->assertRedirect(route('login'));
        $this->get(route('front.booking-summary', ['equipment' => 1, 'start' => '2026-10-12', 'end' => '2026-10-14']))
            ->assertRedirect(route('login'));
        $this->get(route('front.payment', ['equipment' => 1, 'start' => '2026-10-12', 'end' => '2026-10-14']))
            ->assertRedirect(route('login'));
    }

    public function test_catalogue_supports_frontend_capacity_condition_and_location_filters(): void
    {
        $this->get(route('front.catalog', ['min_capacity' => 1000, 'condition' => 'Good']))
            ->assertOk()
            ->assertSee('Portable battery 1000 Wh')
            ->assertDontSee('Portable wind turbine 400 W')
            ->assertSee('Min capacity (Wh)')
            ->assertSee('Condition')
            ->assertSee('Location');

        $this->get(route('front.catalog', ['location' => 'nabeul']))
            ->assertOk()
            ->assertSee('Solar kit 2 × 100 W')
            ->assertDontSee('Portable battery 1000 Wh');
    }
}
