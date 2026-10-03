<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserStoreRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(10);

        return view('pages.admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('pages.admin.users.create');
    }

    public function store(AdminUserStoreRequest $request): RedirectResponse
    {
        // Use the existing register logic but allow role assignment
        $this->userService->register(\App\DTOs\UserRegistrationData::fromRequest($request));

        return redirect()->route('admin.users.index')->with('status', 'user-created');
    }

    public function show(User $user): View
    {
        return view('pages.admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('pages.admin.users.edit', compact('user'));
    }

    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        // Update profile info
        $this->userService->updateUserProfile($user, $request->validated());

        // Update role specifically using the Enum
        $this->userService->updateRole($user, UserRole::from($request->validated('role')));

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->deleteUser($user);

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }
}
