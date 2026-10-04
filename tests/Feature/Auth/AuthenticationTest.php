<?php

use App\Enums\UserRole;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Sign in to SolarShare')
        ->assertSee('id="signin-form"', false)
        ->assertSee('action="'.route('login').'"', false);
});

test('guests must sign in before opening dashboards', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('goes further when we share it.');
    $this->get(route('dashboard.ecommerce'))->assertRedirect(route('login'));
});

test('the front office home stays public and the post-login portal routes users by role', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('goes further when we share it.');
    $this->get(route('dashboard'))->assertRedirect(route('front.my-dashboard'));
    $this->get(route('front.my-dashboard'))->assertOk();
    $this->get(route('dashboard.ecommerce'))->assertForbidden();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('dashboard.ecommerce'));
    $this->get(route('dashboard.ecommerce'))
        ->assertOk()
        ->assertSee('Sign out')
        ->assertSee('xl:flex-1', false)
        ->assertSee('xl:shrink-0', false);

    $owner = User::factory()->create(['role' => UserRole::OWNER]);
    $this->actingAs($owner)->get(route('dashboard'))->assertRedirect(route('front.owner-dashboard'));
    $this->get(route('front.owner-dashboard'))->assertOk()->assertSee('Owner workspace');
    $this->get(route('front.my-dashboard'))->assertForbidden();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('admins are sent to the back office after authentication', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('dashboard'))->assertRedirect(route('dashboard.ecommerce'));
    $this->get(route('admin.users.index'))->assertOk();
});

test('user profile is shown in the front office and saves through the shared profile endpoint', function () {
    $user = User::factory()->create([
        'role' => UserRole::USER,
        'name' => 'Solar User',
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Main navigation')
        ->assertSee('Solar User');

    $this->patch(route('profile.update'), [
        'name' => 'Updated Solar User',
        'email' => $user->email,
        'phone_number' => '+216 20 000 000',
        'address' => 'Tunis',
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->name)->toBe('Updated Solar User')
        ->and($user->fresh()->phone_number)->toBe('+216 20 000 000')
        ->and($user->fresh()->address)->toBe('Tunis');
});

test('admin profile remains in the back office layout', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('sidebar-expanded', false)
        ->assertSee('Profile settings');
});

test('front office account pages require an authenticated verified user', function () {
    $this->get(route('front.my-dashboard'))->assertRedirect(route('login'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});
