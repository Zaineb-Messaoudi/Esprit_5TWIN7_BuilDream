<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserStoreRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

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

        return view('pages.admin.users.index', [
            'users' => $users,
            'title' => __('User Management'),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.users.create', [
            'title' => __('Create User'),
        ]);
    }

    public function store(AdminUserStoreRequest $request): RedirectResponse
    {
        $user = $this->userService->createByAdministrator($request->validated());
        event(new Registered($user));

        return redirect()->route('admin.users.index')->with('status', 'user-created');
    }

    public function show(User $user): View
    {
        return view('pages.admin.users.show', [
            'user' => $user,
            'title' => __('User Details'),
        ]);
    }

    public function edit(User $user): View
    {
        return view('pages.admin.users.edit', [
            'user' => $user,
            'title' => __('Edit User'),
        ]);
    }

    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $emailChanged = $user->email !== $data['email'];

        DB::transaction(function () use ($user, $data, $emailChanged): void {
            if ($user->isAdmin() && $data['role'] !== UserRole::ADMIN->value) {
                $this->ensureAnotherAdministratorRemains($user);
            }

            $this->userService->updateUserProfile($user, $data);

            if ($emailChanged) {
                $user->email_verified_at = null;
                $user->save();
            }
        });

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        DB::transaction(function () use ($user): void {
            $this->ensureAnotherAdministratorRemains($user);
            $this->userService->deleteUser($user);
        });

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }

    private function ensureAnotherAdministratorRemains(User $user): void
    {
        if ((int) auth()->id() === (int) $user->getKey()) {
            throw ValidationException::withMessages([
                'user' => __('You cannot remove your own administrator access or account.'),
            ]);
        }

        if (! $user->isAdmin()) {
            return;
        }

        $adminCount = User::query()
            ->where('role', UserRole::ADMIN->value)
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id'])
            ->count();

        if ($adminCount <= 1) {
            throw ValidationException::withMessages([
                'user' => __('At least one administrator account must remain active.'),
            ]);
        }
    }
}
