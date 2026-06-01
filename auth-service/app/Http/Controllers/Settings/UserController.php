<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * System user management — administrators create, edit and remove the staff
 * accounts that can access the Operations Hub and the back-office services.
 * Customers (client portal accounts) are out of scope here.
 */
class UserController extends Controller
{
    /** @return array<int, string> */
    private function staffRoleValues(): array
    {
        return array_map(fn (UserRole $r): string => $r->value, UserRole::staffRoles());
    }

    /** Number of administrators who can currently sign in. */
    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', 'active')
            ->count();
    }

    /** True if this user currently counts as an active administrator. */
    private function isActiveAdmin(User $user): bool
    {
        return $user->role === UserRole::Admin && $user->status === 'active';
    }

    public function index(): Response
    {
        $users = User::query()
            ->whereIn('role', $this->staffRoleValues())
            ->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'staff' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get()
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role?->value,
                'role_label' => $u->roleLabel(),
                'phone' => $u->phone,
                'status' => $u->status,
                'created_at' => $u->created_at?->toDateString(),
            ]);

        return Inertia::render('settings/Users', [
            'users' => $users,
            'roles' => collect(UserRole::staffRoles())
                ->map(fn (UserRole $r): array => ['value' => $r->value, 'label' => $r->label()])
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($this->staffRoleValues())],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        ActivityLog::record('user.created', "New staff account created: {$user->name}", 'success', $request->user()?->name);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($this->staffRoleValues())],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // Never allow the change to remove the final administrator who can sign
        // in — that would lock everyone out of the role:admin settings forever.
        $willBeActiveAdmin = $data['role'] === UserRole::Admin->value && $data['status'] === 'active';
        if ($this->isActiveAdmin($user) && ! $willBeActiveAdmin && $this->activeAdminCount() <= 1) {
            return back()->withErrors([
                'role' => 'At least one active administrator must remain. Assign another admin first.',
            ]);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        ActivityLog::record('user.updated', "Staff account updated: {$user->name}", 'info', $request->user()?->name);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($this->isActiveAdmin($user) && $this->activeAdminCount() <= 1) {
            return back()->withErrors(['user' => 'You cannot delete the last active administrator.']);
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('user.deleted', "Staff account removed: {$name}", 'warning', $request->user()?->name);

        return back()->with('success', 'User deleted successfully.');
    }
}
