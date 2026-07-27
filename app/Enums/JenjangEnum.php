<?php

namespace App\Enums;

enum JenjangEnum: string
{
    case S1 = 'S1';
    case D3 = 'D3';
    case D4 = 'D4';

    public function label(): string
    {
        return $this->value;
    }
}
