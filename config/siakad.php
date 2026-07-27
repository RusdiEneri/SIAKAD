<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SIAKAD Configurations & Business Rules
    |--------------------------------------------------------------------------
    */

    'ukt_gate_enabled' => (bool) env('SIAKAD_UKT_GATE_ENABLED', true),

    'sks_tiers' => [
        'default_first_semester' => 20,
        'rules' => [
            ['min_gpa' => 3.00, 'max_sks' => 24],
            ['min_gpa' => 2.50, 'max_sks' => 21],
            ['min_gpa' => 2.00, 'max_sks' => 18],
            ['min_gpa' => 0.00, 'max_sks' => 15],
        ],
    ],

    'grade_mappings' => [
        'A'  => ['bobot' => 4.00, 'lulus' => true],
        'A-' => ['bobot' => 3.70, 'lulus' => true],
        'B+' => ['bobot' => 3.30, 'lulus' => true],
        'B'  => ['bobot' => 3.00, 'lulus' => true],
        'B-' => ['bobot' => 2.70, 'lulus' => true],
        'C+' => ['bobot' => 2.30, 'lulus' => true],
        'C'  => ['bobot' => 2.00, 'lulus' => true],
        'D'  => ['bobot' => 1.00, 'lulus' => false],
        'E'  => ['bobot' => 0.00, 'lulus' => false],
    ],
];
