<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    private const PRIVILEGED_ROLES = ['admin', 'manager', 'Admin', 'Manager'];

    public function before(User $user, string $ability): ?bool
    {
        // Admins bypass all checks
        if (in_array($user->role?->name, ['admin', 'Admin'])) {
            return true;
        }

        return null;
    }

    /** Only managers and above can void an order */
    public function void(User $user, Order $order): bool
    {
        return in_array($user->role?->name, self::PRIVILEGED_ROLES);
    }

    /** Only managers and above can delete an order */
    public function delete(User $user, Order $order): bool
    {
        return in_array($user->role?->name, self::PRIVILEGED_ROLES);
    }

    /** Only managers and above can apply a manual discount */
    public function applyDiscount(User $user, Order $order): bool
    {
        return in_array($user->role?->name, self::PRIVILEGED_ROLES);
    }

    /** Cashiers can always view */
    public function view(User $user, Order $order): bool
    {
        return true;
    }

    /** Any authenticated user can create orders */
    public function create(User $user): bool
    {
        return true;
    }

    /** Any authenticated user can update non-destructive order fields */
    public function update(User $user, Order $order): bool
    {
        return true;
    }
}
