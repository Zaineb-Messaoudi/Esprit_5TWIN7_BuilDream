<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('only administrators can access user management', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('user management navigation is only shown to administrators', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $user = User::factory()->create(['role' => UserRole::USER]);

    $this->actingAs($admin)
        ->get(route('profile.overview'))
        ->assertOk()
        ->assertSee(route('admin.users.index'), false);

    $this->actingAs($user)
        ->get(route('profile.overview'))
        ->assertOk()
        ->assertDontSee(route('admin.users.index'), false);
});

test('administrators can create view update and delete users', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $target = User::factory()->create([
        'role' => UserRole::USER,
        'email' => 'old@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee($target->email)
        ->assertSee(route('admin.users.show', $target), false);

    $this->get(route('admin.users.create'))->assertOk()->assertSee(route('admin.users.store'), false);
    $this->get(route('admin.users.show', $target))->assertOk()->assertSee('Standard User');
    $this->get(route('admin.users.edit', $target))->assertOk()->assertSee('value="user"', false);

    $this->post(route('admin.users.store'), [
        'name' => 'New Workspace User',
        'email' => 'new@example.test',
        'phone_number' => '+216 20 000 000',
        'address' => 'Tunis',
        'role' => 'user',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ])->assertRedirect(route('admin.users.index'));

    $created = User::where('email', 'new@example.test')->firstOrFail();
    expect($created->role)->toBe(UserRole::USER)
        ->and($created->hasVerifiedEmail())->toBeFalse()
        ->and(Hash::check('StrongPassword123!', $created->password))->toBeTrue();
    Notification::assertSentTo($created, VerifyEmail::class);

    $this->put(route('admin.users.update', $target), [
        'name' => 'Updated User',
        'email' => 'updated@example.test',
        'phone_number' => '+216 20 111 111',
        'address' => 'Sousse',
        'role' => 'admin',
    ])->assertRedirect(route('admin.users.index'));

    expect($target->fresh()->name)->toBe('Updated User')
        ->and($target->fresh()->role)->toBe(UserRole::ADMIN)
        ->and($target->fresh()->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($target, VerifyEmail::class);

    $this->delete(route('admin.users.destroy', $created))->assertRedirect(route('admin.users.index'));
    expect($created->fresh())->toBeNull();
});

test('administrators cannot remove their own access', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'user',
        ])
        ->assertSessionHasErrors('user');

    $this->delete(route('admin.users.destroy', $admin))
        ->assertSessionHasErrors('user');

    $secondAdmin = User::factory()->create(['role' => UserRole::ADMIN]);
    $this->delete(route('admin.users.destroy', $secondAdmin))
        ->assertRedirect(route('admin.users.index'));

    expect($admin->fresh()->role)->toBe(UserRole::ADMIN)
        ->and($secondAdmin->fresh())->toBeNull();
});

test('guest is redirected before accessing admin user pages', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});
