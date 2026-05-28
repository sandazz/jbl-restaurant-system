<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManagerOverrideService
{
    private const MANAGER_ROLES = ['admin', 'manager', 'Admin', 'Manager'];

    /**
     * Verify that the supplied credentials belong to a user holding a
     * privileged (manager/admin) role.
     *
     * Returns the authenticated manager User on success, null on failure.
     * Does NOT call Auth::login() — this is a one-shot verification only.
     */
    public function verify(string $username, string $password): ?User
    {
        // Allow login by email or by name
        $user = User::where('email', $username)
            ->orWhere('name', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!in_array($user->role?->name, self::MANAGER_ROLES)) {
            return null;
        }

        if ($user->status !== 'active') {
            return null;
        }

        return $user;
    }

    public function isPrivileged(User $user): bool
    {
        return in_array($user->role?->name, self::MANAGER_ROLES);
    }
}
