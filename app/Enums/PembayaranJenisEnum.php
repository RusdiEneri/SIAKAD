<?php

namespace App\Enums;

enum PembayaranJenisEnum: string
{
    case UKT = 'ukt';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::UKT => 'UKT (Uang Kuliah Tunggal)',
            self::LAINNYA => 'Lainnya',
        };
    }
}
