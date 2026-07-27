<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case KAPRODI = 'kaprodi';
    case DOSEN = 'dosen';
    case MAHASISWA = 'mahasiswa';
    case KEUANGAN = 'keuangan';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::KAPRODI => 'Kaprodi',
            self::DOSEN => 'Dosen',
            self::MAHASISWA => 'Mahasiswa',
            self::KEUANGAN => 'Keuangan',
        };
    }
}
