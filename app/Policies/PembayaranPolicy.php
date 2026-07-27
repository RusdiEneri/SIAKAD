<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KEUANGAN, RoleEnum::MAHASISWA]);
    }

    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->hasRole([RoleEnum::ADMIN, RoleEnum::KEUANGAN])) {
            return true;
        }

        return $user->mahasiswa && $user->mahasiswa->id === $pembayaran->mahasiswa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KEUANGAN]);
    }

    public function update(User $user, Pembayaran $pembayaran): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KEUANGAN]);
    }

    public function delete(User $user, Pembayaran $pembayaran): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
