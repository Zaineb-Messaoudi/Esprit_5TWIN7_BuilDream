<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\UserRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('pages.auth.signup');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $userData = UserRegistrationData::fromRequest($request);

        $user = $this->userService->register($userData);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('role.setup');
    }
}
