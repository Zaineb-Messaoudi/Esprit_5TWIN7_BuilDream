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
            if (in_array($slug, ['my-equipment-detail', 'my-equipment-edit', 'buyer-reservation-detail', 'buyer-rental-detail', 'my-reservation-detail', 'my-rental-detail', 'reserve', 'booking-summary', 'payment', 'confirmed', 'invoice'], true)) {
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
            ->assertSee('aria-label="Langue"', false);

        $owner = User::factory()->create([
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('front.owner-dashboard'))
            ->assertOk()
            ->assertSee('Recent reservations')
            ->assertSee('Pending reservation requests');

        $this->get(route('locale.switch', ['locale' => 'en']))->assertRedirect();
        $this->get(route('home'))->assertOk()->assertSee('<html lang="en"', false)->assertSee('Catalog');
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

    public function test_owner_and_buyer_receive_live_workspace_dashboards(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $ownerDashboard = $this->actingAs($owner)->get(route('front.owner-dashboard'))
            ->assertOk()
            ->assertSee('Owner workspace')
            ->assertSee('Equipment listings')
            ->assertSee('Pending reservation requests')
            ->assertSee('Recent reservations')
            ->assertDontSee('Illustrative dashboard data.');

        preg_match('/<nav[^>]*aria-label="Main navigation"[^>]*>(.*?)<\/nav>/s', (string) $ownerDashboard->getContent(), $navigation);
        expect($navigation[1] ?? '')->not->toContain('How it works')->not->toContain('For owners');

        $this->get(route('front.my-reservations'))->assertOk()->assertSee('Owner workspace');
        $this->get(route('front.my-dashboard'))->assertForbidden();
        $this->get(route('front.my-equipment'))->assertOk();

        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $this->actingAs($buyer)->get(route('front.my-dashboard'))
            ->assertOk()
            ->assertSee('Renter workspace')
            ->assertSee('Reservations')
            ->assertSee('Awaiting owner review')
            ->assertSee('Verified payments')
            ->assertDontSee('Buyer dashboard statistics');
        $this->get(route('front.my-equipment'))->assertForbidden();
    }

    public function test_buyer_workspace_screens_render_live_records(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create(['owner_id' => $owner->id]);
        $reservation = \App\Models\Reservation::factory()->create([
            'user_id' => $buyer->id, 'equipment_id' => $equipment->id, 'status' => 'pending',
        ]);
        $rental = \App\Models\Rental::factory()->create([
            'user_id' => $buyer->id, 'equipment_id' => $equipment->id,
        ]);

        $this->actingAs($buyer);
        foreach ([
            ['front.my-reservations', [], $reservation->reference],
            ['front.buyer-reservation-detail', [$reservation->reference], $reservation->reference],
            ['front.my-rentals', [], $rental->reference],
            ['front.buyer-rental-detail', [$rental->reference], $rental->reference],
            ['front.my-contract', [], 'No rental contracts yet.'],
            ['front.my-extensions', [], 'No extension requests yet.'],
            ['front.my-payments', [], 'No payments yet.'],
            ['front.my-notifications', [], 'Your updates'],
        ] as [$route, $parameters, $content]) {
            $response = $this->get(route($route, $parameters))->assertOk()->assertSee($content);
            if ($route !== 'front.my-notifications') {
                $response->assertDontSee('Frontend preview with sample data.');
            }
            $response->assertSee('Buyer account navigation');
        }

        $this->get(route('front.my-rentals'))->assertSee('Extensions')->assertSee('Payments & invoices');
        $this->get(route('front.my-payments'))->assertSee('Invoices')->assertSee('No invoices yet.');
    }

    public function test_owner_workspace_pages_show_live_reservations_and_rentals(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create(['owner_id' => $owner->id]);
        $reservation = \App\Models\Reservation::factory()->create([
            'equipment_id' => $equipment->id, 'user_id' => $buyer->id, 'status' => 'pending',
        ]);
        $rental = \App\Models\Rental::factory()->create([
            'equipment_id' => $equipment->id, 'user_id' => $buyer->id,
        ]);

        $this->actingAs($owner);
        $this->get(route('front.my-reservations'))
            ->assertOk()->assertSee($reservation->reference)->assertSee('Approve')->assertSee('Decline');
        $this->get(route('front.my-reservation-detail', $reservation->reference))
            ->assertOk()->assertSee($reservation->reference);
        $this->get(route('front.my-rentals'))
            ->assertOk()->assertSee($rental->reference);
        $this->get(route('front.my-rental-detail', $rental->reference))
            ->assertOk()->assertSee($rental->reference);
        $this->get(route('front.my-contract'))->assertOk()->assertSee('No rental contracts yet.');
        $this->get(route('front.my-extensions'))->assertOk()->assertSee('No extension requests yet.');
        $this->get(route('front.my-earnings'))->assertOk()->assertSee('Invoices');
        $this->get(route('front.my-calendar'))->assertOk()->assertSee('SERVICE');
        $this->get(route('front.my-maintenance'))->assertRedirect(route('technical.maintenances.index'));
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
