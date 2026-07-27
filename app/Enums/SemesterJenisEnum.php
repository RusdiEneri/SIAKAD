<?php

namespace App\Enums;

enum SemesterJenisEnum: string
{
    case GANJIL = 'ganjil';
    case GENAP = 'genap';
    case PENDEK = 'pendek';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
