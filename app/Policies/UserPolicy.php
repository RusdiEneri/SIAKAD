<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) && $user->id !== $model->id;
    }
}
