<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function update(User $user, Customer $customer): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager], true);
    }

    public function restoreAny(User $user): bool
    {
        return $user->role !== UserRole::Medewerker;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->role !== UserRole::Medewerker;
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->role !== UserRole::Medewerker;
    }
}
