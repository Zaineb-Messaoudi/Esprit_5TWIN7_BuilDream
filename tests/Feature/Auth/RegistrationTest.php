<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Sign Up')
        ->assertSee('id="register-form"', false)
        ->assertSee('action="'.route('register').'"', false)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('name="profile_photo"', false)
        ->assertSee('name="phone_number"', false)
        ->assertSee('name="address"', false)
        ->assertSee('name="password_confirmation"', false);
});

test('new users can register', function () {
    Notification::fake();
    Storage::fake('public');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone_number' => '+1 555 0100',
        'address' => '1 Solar Way',
        'profile_photo' => UploadedFile::fake()->image('profile.jpg'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->firstOrFail();
    expect($user->role->value)->toBe('user')
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->phone_number)->toBe('+1 555 0100')
        ->and($user->address)->toBe('1 Solar Way')
        ->and($user->profile_photo_path)->not->toBeNull()
        ->and($user->profile_photo_url)->toBe(Storage::disk('public')->url($user->profile_photo_path));
    Storage::disk('public')->assertExists($user->profile_photo_path);
    Notification::assertSentTo($user, VerifyEmail::class);
    $this->get('/dashboard')->assertRedirect(route('verification.notice'));
});

test('registration can use the default profile photo when no photo is uploaded', function () {
    Notification::fake();
    Storage::fake('public');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->profile_photo_path)->toBeNull()
        ->and($user->profile_photo_url)->toBe(asset('images/user/owner.png'));
});
