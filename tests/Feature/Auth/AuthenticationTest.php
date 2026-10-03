<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Sign In')
        ->assertSee('id="signin-form"', false)
        ->assertSee('action="'.route('login').'"', false);
});

test('guests must sign in before opening dashboards', function () {
    $this->get('/')->assertRedirect(route('login'));
    $this->get(route('dashboard.ecommerce'))->assertRedirect(route('login'));
});

test('signed-in users are sent to the ecommerce dashboard from home', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('dashboard.ecommerce'));

    $this->get(route('dashboard.ecommerce'))
        ->assertOk()
        ->assertSee('xl:flex-1', false)
        ->assertSee('xl:shrink-0', false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
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
    $response->assertRedirect(route('login'));
});
