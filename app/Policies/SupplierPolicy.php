<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function update(User $user, Supplier $supplier): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager], true);
    }

    public function restoreAny(User $user): bool
    {
        return $user->role !== UserRole::Medewerker;
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->role !== UserRole::Medewerker;
    }

    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->role !== UserRole::Medewerker;
    }
}
