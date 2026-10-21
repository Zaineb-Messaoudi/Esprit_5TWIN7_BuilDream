<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

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

        $user = $request->user();
        $selectedRole = UserRole::from($validated['role']);

        $user->forceFill([
            'role' => $selectedRole,
            'role_setup_completed' => true,
        ])->save();

        // Assign Spatie role
        $spatieRoleName = $selectedRole->value;
        $spatieRole = Role::where('name', $spatieRoleName)->where('guard_name', 'web')->first();

        if ($spatieRole) {
            $user->syncRoles([$spatieRole]);
        }

        return redirect()->route('dashboard');
    }
}
