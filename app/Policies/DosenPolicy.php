<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Dosen;
use App\Models\User;

class DosenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI]);
    }

    public function view(User $user, Dosen $dosen): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $dosen->prodi_id;
        }

        return $user->id === $dosen->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, Dosen $dosen): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) || $user->id === $dosen->user_id;
    }

    public function delete(User $user, Dosen $dosen): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
