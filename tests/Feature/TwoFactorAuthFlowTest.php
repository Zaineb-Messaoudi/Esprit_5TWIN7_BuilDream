<?php

use App\Enums\UserRole;
use App\Models\User;

it('requires a session verification before opening protected web routes', function () {
    $user = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('two-factor.verify'));
});

it('does not block the two-factor setup route itself', function () {
    $user = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);

    $this->actingAs($user)
        ->get(route('two-factor.setup'))
        ->assertOk();
});
