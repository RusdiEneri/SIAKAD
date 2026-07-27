<?php

namespace App\Enums;

enum NilaiHurufEnum: string
{
    case A = 'A';
    case A_MINUS = 'A-';
    case B_PLUS = 'B+';
    case B = 'B';
    case B_MINUS = 'B-';
    case C_PLUS = 'C+';
    case C = 'C';
    case D = 'D';
    case E = 'E';

    public function bobot(): float
    {
        $mappings = config('siakad.grade_mappings', []);
        return $mappings[$this->value]['bobot'] ?? 0.00;
    }

    public function isLulus(): bool
    {
        $mappings = config('siakad.grade_mappings', []);
        return $mappings[$this->value]['lulus'] ?? false;
    }
}
