<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SpecialPageController extends Controller
{
    private const PAGES = [
        'error-403' => [
            'title' => '403 · Access forbidden',
            'heading' => 'You do not have permission to view this page.',
            'message' => 'Your account does not have access to this demo route. If you think this is a mistake, contact your workspace administrator.',
            'code' => '403',
            'tone' => 'warning',
            'action' => 'Back to dashboard',
        ],
        'error-500' => [
            'title' => '500 · Server error',
            'heading' => 'Something went wrong on our end.',
            'message' => 'This is a static error-page example. It does not represent the current application health.',
            'code' => '500',
            'tone' => 'error',
            'action' => 'Return home',
        ],
        'error-503' => [
            'title' => '503 · Service unavailable',
            'heading' => 'Service is temporarily unavailable.',
            'message' => 'This sample page can be used for planned maintenance or temporary service interruptions.',
            'code' => '503',
            'tone' => 'warning',
            'action' => 'Try the dashboard',
        ],
        'access-denied' => [
            'title' => 'Access denied',
            'heading' => 'This area is restricted.',
            'message' => 'Check that you are signed in with the right account or ask an administrator for access.',
            'code' => 'Restricted',
            'tone' => 'warning',
            'action' => 'Return home',
        ],
        'maintenance' => [
            'title' => 'Maintenance',
            'heading' => 'We are making things better.',
            'message' => 'This is a maintenance-page design example. Your application is not in maintenance mode.',
            'code' => 'Maintenance',
            'tone' => 'primary',
            'action' => 'Back to dashboard',
        ],
        'coming-soon' => [
            'title' => 'Coming soon',
            'heading' => 'Something useful is on the way.',
            'message' => 'Use this page as a styled launch placeholder while a real feature is being prepared.',
            'code' => 'Soon',
            'tone' => 'primary',
            'action' => 'Explore dashboard',
        ],
        'success' => [
            'title' => 'Success',
            'heading' => 'Your demo action is complete.',
            'message' => 'This confirmation is for the UI example only. No external action or data change took place.',
            'code' => 'Done',
            'tone' => 'success',
            'action' => 'Continue',
        ],
        'under-construction' => [
            'title' => 'Under construction',
            'heading' => 'This page is being prepared.',
            'message' => 'The construction layout is provided as a reusable example and does not indicate unfinished production work.',
            'code' => 'Build',
            'tone' => 'warning',
            'action' => 'Back to dashboard',
        ],
        'session-expired' => [
            'title' => 'Session expired',
            'heading' => 'Please sign in again.',
            'message' => 'This is a static session-expired page example and does not end your current authenticated session.',
            'code' => 'Session',
            'tone' => 'warning',
            'action' => 'Sign in',
            'action_route' => 'login',
        ],
        'auth-error' => [
            'title' => 'Authentication error',
            'heading' => 'We could not complete sign in.',
            'message' => 'Review your credentials and try again. This demonstration does not perform authentication.',
            'code' => 'Auth',
            'tone' => 'error',
            'action' => 'Go to sign in',
            'action_route' => 'login',
        ],
        'logout-confirmation' => [
            'title' => 'Signed out',
            'heading' => 'You have safely left the demo page.',
            'message' => 'This confirmation page is illustrative. Use the application’s actual sign-out control to end a session.',
            'code' => 'Bye',
            'tone' => 'success',
            'action' => 'Sign in',
            'action_route' => 'login',
        ],
    ];

    public function show(string $page): View
    {
        abort_unless(isset(self::PAGES[$page]), 404);

        return view('pages.special.status', [
            'page' => self::PAGES[$page],
        ]);
    }
}
