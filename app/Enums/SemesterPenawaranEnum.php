<?php

namespace App\Enums;

enum SemesterPenawaranEnum: string
{
    case GANJIL = 'ganjil';
    case GENAP = 'genap';
    case SEMUA = 'semua';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
