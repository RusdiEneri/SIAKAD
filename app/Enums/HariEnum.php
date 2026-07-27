<?php

namespace App\Enums;

enum HariEnum: string
{
    case SENIN = 'senin';
    case SELASA = 'selasa';
    case RABU = 'rabu';
    case KAMIS = 'kamis';
    case JUMAT = 'jumat';
    case SABTU = 'sabtu';
    case MINGGU = 'minggu';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
