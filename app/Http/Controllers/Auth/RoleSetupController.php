<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleSetupController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        if ($request->user()->role_setup_completed) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.role-setup');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:buyer,owner'],
        ]);

        $request->user()->forceFill([
            'role' => UserRole::from($validated['role']),
            'role_setup_completed' => true,
        ])->save();

        return redirect()->route('dashboard');
    }
}
