<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
        'services.facebook.client_id' => 'facebook-app-id',
        'services.facebook.client_secret' => 'facebook-app-secret',
        'services.facebook.redirect' => 'http://localhost:8000/auth/facebook/callback',
        'services.facebook.graph_version' => 'v23.0',
    ]);
});

test('sign in page offers Google and Facebook OAuth', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('social.redirect', 'google'), false)
        ->assertSee(route('social.redirect', 'facebook'), false)
        ->assertSee('Sign in with Facebook');
});

test('sign up page offers Google and Facebook without asking for a role', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee(route('social.redirect', 'google'), false)
        ->assertSee(route('social.redirect', 'facebook'), false)
        ->assertDontSee('How will you use SolarShare?')
        ->assertDontSee('name="role"', false)
        ->assertDontSee('Sign up with X');
});

test('Google OAuth creates a verified account then requires role selection', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-token']),
        'openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-user-123',
            'email' => 'owner@example.test',
            'email_verified' => true,
            'name' => 'Solar Owner',
        ]),
    ]);

    $this->get(route('social.redirect', 'google'))
        ->assertRedirectContains('accounts.google.com');

    $state = $this->app['session.store']->get('social_auth.state');
    expect($state)->toBeString()->not->toBeEmpty();

    $this->get(route('social.callback', [
        'provider' => 'google',
        'state' => $state,
        'code' => 'authorization-code',
    ]))->assertRedirect(route('role.setup', absolute: false));

    $user = User::where('email', 'owner@example.test')->firstOrFail();
    expect($user->google_id)->toBe('google-user-123')
        ->and($user->role)->toBe(UserRole::BUYER)
        ->and($user->role_setup_completed)->toBeFalse()
        ->and($user->hasVerifiedEmail())->toBeTrue();
    $this->assertAuthenticatedAs($user);
    $this->post(route('role.setup.store'), ['role' => 'owner'])
        ->assertRedirect(route('dashboard', absolute: false));
    expect($user->fresh()->role)->toBe(UserRole::OWNER)
        ->and($user->fresh()->role_setup_completed)->toBeTrue();
});

test('Facebook OAuth creates a buyer and rejects callbacks with invalid state', function () {
    Http::fake([
        'graph.facebook.com/v23.0/oauth/access_token*' => Http::response(['access_token' => 'facebook-access-token']),
        'graph.facebook.com/me*' => Http::response([
            'id' => 'facebook-user-456',
            'email' => 'buyer@example.test',
            'name' => 'Solar Buyer',
        ]),
    ]);

    $this->get(route('social.redirect', 'facebook'))
        ->assertRedirectContains('facebook.com');
    $state = $this->app['session.store']->get('social_auth.state');

    $this->get(route('social.callback', [
        'provider' => 'facebook',
        'state' => $state,
        'code' => 'authorization-code',
    ]))->assertRedirect(route('role.setup', absolute: false));

    $user = User::where('email', 'buyer@example.test')->firstOrFail();
    expect($user->facebook_id)->toBe('facebook-user-456')
        ->and($user->role)->toBe(UserRole::BUYER)
        ->and($user->role_setup_completed)->toBeFalse()
        ->and($user->hasVerifiedEmail())->toBeTrue();
    $this->post(route('role.setup.store'), ['role' => 'buyer'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->post(route('logout'));
    Http::fake();
    $this->get(route('social.callback', [
        'provider' => 'facebook',
        'state' => 'invalid-state',
        'code' => 'another-code',
    ]))->assertRedirect(route('login'));
    Http::assertNothingSent();
});
