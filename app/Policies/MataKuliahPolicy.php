<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\MataKuliah;
use App\Models\User;

class MataKuliahPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN, RoleEnum::MAHASISWA]);
    }

    public function view(User $user, MataKuliah $mataKuliah): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI]);
    }

    public function update(User $user, MataKuliah $mataKuliah): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $mataKuliah->prodi_id;
        }

        return false;
    }

    public function delete(User $user, MataKuliah $mataKuliah): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
