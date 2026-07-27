<?php

namespace App\Enums;

enum PembayaranStatusEnum: string
{
    case BELUM = 'belum';
    case SEBAGIAN = 'sebagian';
    case LUNAS = 'lunas';

    public function label(): string
    {
        return match ($this) {
            self::BELUM => 'Belum Bayar',
            self::SEBAGIAN => 'Bayar Sebagian',
            self::LUNAS => 'Lunas',
        };
    }
}
