<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Prodi;
use App\Models\User;

class ProdiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN]);
    }

    public function view(User $user, Prodi $prodi): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, Prodi $prodi): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function delete(User $user, Prodi $prodi): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
