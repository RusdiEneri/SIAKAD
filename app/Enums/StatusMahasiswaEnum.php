<?php

namespace App\Enums;

enum StatusMahasiswaEnum: string
{
    case AKTIF = 'aktif';
    case CUTI = 'cuti';
    case LULUS = 'lulus';
    case DO = 'do';
    case NONAKTIF = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::CUTI => 'Cuti',
            self::LULUS => 'Lulus',
            self::DO => 'Drop Out (DO)',
            self::NONAKTIF => 'Non-Aktif',
        };
    }
}
