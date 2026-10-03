<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AuthDemoController extends Controller
{
    public function show(string $demo): View
    {
        $pages = [
            'lock-screen' => [
                'title' => 'Lock Screen',
                'eyebrow' => 'Welcome back',
                'heading' => 'Unlock your workspace',
                'description' => 'Enter your password to preview the lock-screen interaction. This demo does not authenticate or unlock an account.',
                'field' => 'Password',
                'button' => 'Unlock demo',
                'success' => 'Demo interaction complete. No account was unlocked.',
                'mode' => 'password',
            ],
            'two-factor' => [
                'title' => 'Two-Factor Verification',
                'eyebrow' => 'Account security',
                'heading' => 'Verify your identity',
                'description' => 'Enter any six digits to preview the verification screen. No code is sent or verified.',
                'field' => '6-digit verification code',
                'button' => 'Verify demo code',
                'success' => 'Demo code accepted for preview only. No identity was verified.',
                'mode' => 'code',
            ],
        ];

        abort_unless(isset($pages[$demo]), 404);

        return view('pages.special.auth-demo', [
            'demo' => $pages[$demo],
        ]);
    }
}
