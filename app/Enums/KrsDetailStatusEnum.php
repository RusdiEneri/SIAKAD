<?php

namespace App\Enums;

enum KrsDetailStatusEnum: string
{
    case DIAMBIL = 'diambil';
    case BATAL = 'batal';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
